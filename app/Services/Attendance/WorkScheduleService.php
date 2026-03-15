<?php

namespace App\Services\Attendance;

use App\Models\WorkSchedule;
use App\Models\WorkScheduleDay;
use Illuminate\Support\Facades\DB;

class WorkScheduleService
{
    public function getAll()
    {
        return WorkSchedule::with('days')
            ->latest()
            ->get();
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $schedule = WorkSchedule::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true
            ]);

            if (!empty($data['days'])) {

                foreach ($data['days'] as $day => $dayData) {

                    WorkScheduleDay::create([
                        'work_schedule_id' => $schedule->id,
                        'day_of_week' => $day,

                        'start_time' => $dayData['start_time'] ?? null,
                        'end_time' => $dayData['end_time'] ?? null,

                        'break_start' => $dayData['break_start'] ?? null,
                        'break_end' => $dayData['break_end'] ?? null,

                        'work_hours' => $dayData['work_hours'] ?? 0,
                        'tolerance_late' => $dayData['tolerance_late'] ?? 0,
                        'tolerance_early_leave' => $dayData['tolerance_early_leave'] ?? 0,

                        'is_working_day' => $dayData['is_working_day'] ?? false
                    ]);
                }
            }

            return $schedule->load('days');
        });
    }

    public function update(WorkSchedule $schedule, array $data)
    {
        return DB::transaction(function () use ($schedule, $data) {

            $schedule->update([
                'name' => $data['name'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true
            ]);

            // hapus semua hari lama
            $schedule->days()->delete();

            // pastikan semua hari 1-7 tersimpan
            for ($day = 1; $day <= 7; $day++) {

                $dayData = $data['days'][$day] ?? [];

                $schedule->days()->create([
                    'day_of_week' => $day,

                    'start_time' => $dayData['start_time'] ?? null,
                    'end_time' => $dayData['end_time'] ?? null,

                    'break_start' => $dayData['break_start'] ?? null,
                    'break_end' => $dayData['break_end'] ?? null,

                    'work_hours' => $dayData['work_hours'] ?? 0,

                    'tolerance_late' => $dayData['tolerance_late'] ?? 0,
                    'tolerance_early_leave' => $dayData['tolerance_early_leave'] ?? 0,

                    'is_working_day' => $dayData['is_working_day'] ?? 0
                ]);
            }

            return $schedule->load('days');
        });
    }

    public function delete(WorkSchedule $schedule)
    {
        return $schedule->delete();
    }
}
