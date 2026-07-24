<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\WorkSchedule;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group Jadwal & Libur
 *
 * Work schedule dan holiday management yang read-only untuk mobile app.
 */
class ApiScheduleController extends Controller
{
    use ApiResponse;

    /**
     * Get work schedule for current employee
     *
     * Returns employee's assigned schedule or default active schedule.
     */
    public function schedule(Request $request)
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->error(
                errors: 'Akun tidak memiliki data pegawai.',
                message: 'Data pegawai tidak ditemukan',
                status: 404
            );
        }

        $schedule = $employee->workSchedule()->with('days')->first()
            ?? WorkSchedule::where('is_active', true)->with('days')->first();

        if (! $schedule) {
            return $this->error(
                errors: 'Jadwal kerja tidak ditemukan',
                message: 'Jadwal kerja belum dikonfigurasi',
                status: 404
            );
        }

        return $this->success([
            'id' => $schedule->id,
            'name' => $schedule->name,
            'days' => $schedule->days->map(fn ($day) => [
                'day_of_week' => $day->day_of_week,
                'day_name' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][$day->day_of_week - 1],
                'is_working_day' => $day->is_working_day,
                'start_time' => $day->start_time,
                'end_time' => $day->end_time,
                'tolerance_late' => $day->tolerance_late,
                'tolerance_early_leave' => $day->tolerance_early_leave,
            ])->sortBy('day_of_week')->values(),
        ], 'Jadwal kerja karyawan');
    }

    /**
     * Get holidays for specified month/year
     *
     * Defaults to current month if not specified.
     */
    public function holidays(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $year = $request->integer('year') ?: now()->year;
        $month = $request->integer('month') ?: now()->month;

        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();

        $holidays = Holiday::whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->select('holiday_date', 'name')
            ->orderBy('holiday_date')
            ->get()
            ->map(fn ($holiday) => [
                'date' => $holiday->holiday_date->toDateString(),
                'name' => $holiday->name,
            ]);

        return $this->success([
            'year' => $year,
            'month' => $month,
            'holidays' => $holidays,
        ], 'Daftar hari libur');
    }
}
