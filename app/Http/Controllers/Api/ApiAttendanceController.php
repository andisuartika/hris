<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Services\Attendance\AttendanceService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @group Absensi
 *
 * Check-in/out, riwayat, rekap, dan pengaturan mode absensi.
 */
class ApiAttendanceController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function checkin(Request $request)
    {
        // Kewajiban lokasi/wajah divalidasi di service sesuai verification_mode perusahaan
        $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'face_embedding' => 'nullable|array',
            'face_embedding.*' => 'numeric',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {

            $attendance = $this->attendanceService->checkin(
                $request->user(),
                $this->payload($request)
            );

            return $this->success(
                new AttendanceResource($attendance),
                'Checkin berhasil'
            );
        } catch (\Exception $e) {

            return $this->error($e->getMessage());
        }
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'face_embedding' => 'nullable|array',
            'face_embedding.*' => 'numeric',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {

            $attendance = $this->attendanceService->checkout(
                $request->user(),
                $this->payload($request)
            );

            return $this->success(
                new AttendanceResource($attendance),
                'Checkout berhasil'
            );
        } catch (\Exception $e) {

            return $this->error($e->getMessage());
        }
    }

    public function today(Request $request)
    {
        $attendance = $this->attendanceService->today($request->user());

        return $this->success(
            $attendance ? new AttendanceResource($attendance) : null,
            'Attendance today'
        );
    }

    public function history(Request $request)
    {
        $attendances = $this->attendanceService->history($request->user());

        return $this->paginated($attendances);
    }

    public function summary(Request $request)
    {
        $request->validate([
            'year'  => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $summary = $this->attendanceService->summary(
            $request->user(),
            $request->integer('year') ?: now()->year,
            $request->integer('month') ?: now()->month
        );

        return $this->success($summary, 'Rekap absensi bulanan');
    }

    /**
     * Pengaturan absensi perusahaan (mobile menyesuaikan UI berdasarkan mode).
     */
    public function settings(Request $request)
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->error(
                errors: 'Akun tidak memiliki data pegawai.',
                message: 'Data pegawai tidak ditemukan',
                status: 404
            );
        }

        $setting = \App\Models\AttendanceSetting::forCompany($employee->company_id);

        return $this->success([
            'verification_mode'    => $setting->verification_mode,
            'requires_location'    => $setting->requiresLocation(),
            'requires_face'        => $setting->requiresFace(),
            'face_match_threshold' => $setting->face_match_threshold,
        ], 'Pengaturan absensi');
    }

    /**
     * Ajukan koreksi absensi (lupa check-in/out, jam salah). Disetujui admin via web.
     */
    public function storeCorrection(Request $request)
    {
        $data = $request->validate([
            'work_date' => 'required|date|before_or_equal:today',
            'requested_clock_in' => 'nullable|date_format:H:i',
            'requested_clock_out' => 'nullable|date_format:H:i|required_without:requested_clock_in',
            'reason' => 'required|string|max:1000',
        ]);

        $employee = $request->user()->employee;

        $exists = \App\Models\AttendanceCorrection::where('employee_id', $employee->id)
            ->where('work_date', $data['work_date'])
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return $this->error('Masih ada pengajuan koreksi pending untuk tanggal tersebut');
        }

        $attendance = \App\Models\Attendance::where('employee_id', $employee->id)
            ->where('work_date', $data['work_date'])
            ->first();

        $correction = \App\Models\AttendanceCorrection::create([
            'employee_id' => $employee->id,
            'attendance_id' => $attendance?->id,
            'work_date' => $data['work_date'],
            'requested_clock_in' => isset($data['requested_clock_in'])
                ? $data['work_date'].' '.$data['requested_clock_in'] : null,
            'requested_clock_out' => isset($data['requested_clock_out'])
                ? $data['work_date'].' '.$data['requested_clock_out'] : null,
            'reason' => $data['reason'],
        ]);

        return $this->success($correction, 'Pengajuan koreksi absensi dikirim');
    }

    /**
     * Riwayat pengajuan koreksi absensi.
     */
    public function corrections(Request $request)
    {
        return $this->paginated(
            \App\Models\AttendanceCorrection::with('reviewer:id,name')
                ->where('employee_id', $request->user()->employee->id)
                ->latest()
                ->paginate(10)
        );
    }

    private function payload(Request $request): array
    {
        $data = $request->only('latitude', 'longitude', 'face_embedding');

        if ($request->hasFile('photo')) {
            // Selfie bukti kehadiran, disimpan di disk privat
            $data['photo_path'] = $request->file('photo')->store('attendance/photos', 'local');
        }

        return $data;
    }
}



