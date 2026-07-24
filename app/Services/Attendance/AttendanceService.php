<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\OfficeLocation;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkScheduleDay;
use App\Services\Leave\LeaveService;
use Carbon\Carbon;

class AttendanceService
{
    public function __construct(
        protected LeaveService $leaveService
    ) {}

    public function checkin(User $user, array $data): Attendance
    {
        $employee = $user->employee;

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('work_date', today())
            ->first();

        if ($attendance) {
            throw new \Exception('Anda sudah melakukan checkin hari ini');
        }

        $setting = AttendanceSetting::forCompany($employee->company_id);

        $this->ensureWorkingDay($employee);
        $verification = $this->verify($setting, $employee, $data);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'clock_in_lat' => $data['latitude'] ?? null,
            'clock_in_lng' => $data['longitude'] ?? null,
            'face_verified' => $verification['face_score'] !== null,
            'status' => $this->isLate($employee) ? 'late' : 'present',
        ]);

        $this->log($attendance, 'clock_in', $data, $verification);

        return $attendance;
    }

    public function checkout(User $user, array $data): Attendance
    {
        $employee = $user->employee;

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('work_date', today())
            ->firstOrFail();

        if ($attendance->clock_out_at) {
            throw new \Exception('Anda sudah checkout');
        }

        $setting = AttendanceSetting::forCompany($employee->company_id);
        $verification = $this->verify($setting, $employee, $data);

        $updates = [
            'clock_out_at' => now(),
            'clock_out_lat' => $data['latitude'] ?? null,
            'clock_out_lng' => $data['longitude'] ?? null,
        ];

        // Pulang cepat hanya menimpa status 'present' — status 'late' dipertahankan
        if ($attendance->status === 'present' && $this->isEarlyLeave($employee)) {
            $updates['status'] = 'early_leave';
        }

        $attendance->update($updates);
        $this->log($attendance, 'clock_out', $data, $verification);

        return $attendance;
    }

    public function today(User $user)
    {
        return Attendance::where('employee_id', $user->employee->id)
            ->where('work_date', today())
            ->first();
    }

    public function history(User $user)
    {
        return Attendance::where('employee_id', $user->employee->id)
            ->latest()
            ->paginate(10);
    }

    /**
     * Rekap bulanan: jumlah per status + alpha (hari kerja terlewat tanpa absensi).
     */
    public function summary(User $user, int $year, int $month): array
    {
        $employee = $user->employee;
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();
        $untilDate = $end->isFuture() ? today() : $end;

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $counts = $attendances->countBy('status');

        $holidayDates = Holiday::whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->pluck('holiday_date')
            ->map(fn ($d) => $d->toDateString())
            ->all();

        $workingDayNumbers = $this->scheduleFor($employee)?->days
            ->where('is_working_day', true)
            ->pluck('day_of_week')
            ->all() ?? [1, 2, 3, 4, 5];

        $attendedDates = $attendances->pluck('work_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->all();

        $leaveDates = $this->leaveService->approvedLeaveDates($employee, $start, $end);

        $workingDays = 0;
        $absent = 0;
        $onLeave = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $isWorkingDay = in_array($date->dayOfWeekIso, $workingDayNumbers)
                && ! in_array($date->toDateString(), $holidayDates);

            if (! $isWorkingDay) {
                continue;
            }

            $workingDays++;

            if (in_array($date->toDateString(), $leaveDates)) {
                $onLeave++;
                continue;
            }

            if ($date->lt($untilDate) && ! in_array($date->toDateString(), $attendedDates)) {
                $absent++;
            }
        }

        return [
            'year' => $year,
            'month' => $month,
            'working_days' => $workingDays,
            'present' => $counts->get('present', 0),
            'late' => $counts->get('late', 0),
            'early_leave' => $counts->get('early_leave', 0),
            'leave' => $onLeave,
            'absent' => $absent + $counts->get('absent', 0),
        ];
    }

    // ==========================================
    // HELPER: JADWAL & STATUS
    // ==========================================

    private function scheduleFor(Employee $employee): ?WorkSchedule
    {
        return $employee->workSchedule()->with('days')->first()
            ?? WorkSchedule::with('days')->where('is_active', true)->first();
    }

    private function todayScheduleDay(Employee $employee): ?WorkScheduleDay
    {
        return $this->scheduleFor($employee)?->days
            ->firstWhere('day_of_week', now()->dayOfWeekIso);
    }

    private function ensureWorkingDay(Employee $employee): void
    {
        $holiday = Holiday::where('holiday_date', today()->toDateString())->first();
        if ($holiday) {
            throw new \Exception("Hari ini libur: {$holiday->name}");
        }

        $day = $this->todayScheduleDay($employee);
        if ($day && ! $day->is_working_day) {
            throw new \Exception('Hari ini bukan hari kerja sesuai jadwal Anda');
        }

        if ($this->leaveService->isOnApprovedLeave($employee, today())) {
            throw new \Exception('Anda sedang cuti pada tanggal ini');
        }
    }

    private function isLate(Employee $employee): bool
    {
        $day = $this->todayScheduleDay($employee);

        if (! $day || ! $day->start_time) {
            return false;
        }

        $limit = Carbon::parse(today()->toDateString().' '.$day->start_time)
            ->addMinutes((int) $day->tolerance_late);

        return now()->gt($limit);
    }

    private function isEarlyLeave(Employee $employee): bool
    {
        $day = $this->todayScheduleDay($employee);

        if (! $day || ! $day->end_time) {
            return false;
        }

        $limit = Carbon::parse(today()->toDateString().' '.$day->end_time)
            ->subMinutes((int) $day->tolerance_early_leave);

        return now()->lt($limit);
    }

    // ==========================================
    // HELPER: VERIFIKASI SESUAI MODE
    // ==========================================

    /**
     * Validasi lokasi & wajah sesuai mode. Mengembalikan hasil untuk audit log.
     * @return array{in_radius: bool, face_score: ?float}
     */
    private function verify(AttendanceSetting $setting, Employee $employee, array $data): array
    {
        $inRadius = false;
        $faceScore = null;

        if ($setting->requiresLocation()) {
            if (! isset($data['latitude'], $data['longitude'])) {
                throw new \Exception('Lokasi (latitude/longitude) wajib dikirim');
            }

            $this->ensureWithinOfficeRadius($employee, (float) $data['latitude'], (float) $data['longitude']);
            $inRadius = true;
        }

        if ($setting->requiresFace()) {
            $faceScore = $this->verifyFace($employee, $data, (int) $setting->face_match_threshold);
        }

        return ['in_radius' => $inRadius, 'face_score' => $faceScore];
    }

    /**
     * Verifikasi wajah server-side: cosine similarity antara embedding yang
     * dikirim mobile dengan template terdaftar, dibandingkan threshold (0-100).
     */
    private function verifyFace(Employee $employee, array $data, int $threshold): float
    {
        if (empty($data['face_embedding']) || ! is_array($data['face_embedding'])) {
            throw new \Exception('Data wajah (face_embedding) wajib dikirim untuk absensi');
        }

        $template = $employee->faceTemplate?->template_data;

        if (! $template) {
            throw new \Exception('Wajah Anda belum terdaftar. Silakan registrasi wajah terlebih dahulu');
        }

        if (count($template) !== count($data['face_embedding'])) {
            throw new \Exception('Format data wajah tidak sesuai dengan template terdaftar');
        }

        $score = round($this->cosineSimilarity($template, $data['face_embedding']) * 100, 2);

        if ($score < $threshold) {
            throw new \Exception("Verifikasi wajah gagal (skor {$score}, minimal {$threshold})");
        }

        return $score;
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = $normA = $normB = 0.0;
        foreach ($a as $i => $v) {
            $dot += $v * $b[$i];
            $normA += $v ** 2;
            $normB += $b[$i] ** 2;
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    private function log(Attendance $attendance, string $type, array $data, array $verification): void
    {
        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'type' => $type,
            'timestamp' => now(),
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'is_in_radius' => $verification['in_radius'],
            'face_similarity_score' => $verification['face_score'],
            'photo_path' => $data['photo_path'] ?? null,
        ]);
    }

    // ==========================================
    // HELPER: LOKASI
    // ==========================================

    private function ensureWithinOfficeRadius(Employee $employee, float $lat, float $lng): void
    {
        $locationIds = $employee->office_locations ?: array_filter([$employee->office_location_id]);
        $locations = OfficeLocation::whereIn('id', $locationIds)->get();

        if ($locations->isEmpty()) {
            return; // pegawai tanpa lokasi kantor: tidak dibatasi radius
        }

        $nearest = null;
        foreach ($locations as $location) {
            $distance = $this->distanceMeters($lat, $lng, (float) $location->latitude, (float) $location->longitude);

            if ($distance <= (float) $location->radius_meters) {
                return;
            }

            if ($nearest === null || $distance < $nearest['distance']) {
                $nearest = ['name' => $location->name, 'distance' => $distance];
            }
        }

        throw new \Exception(sprintf(
            'Anda berada di luar area kantor. Jarak ke %s: %d meter',
            $nearest['name'],
            round($nearest['distance'])
        ));
    }

    // Haversine
    private function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
