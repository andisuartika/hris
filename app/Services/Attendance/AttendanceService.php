<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\User;

class AttendanceService
{
    public function checkin(User $user, array $data): Attendance
    {
        $employee = $user->employee;

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('work_date', today())
            ->first();

        if ($attendance) {
            throw new \Exception('Anda sudah melakukan checkin hari ini');
        }

        return Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'clock_in_lat' => $data['latitude'],
            'clock_in_lng' => $data['longitude'],
            'face_verified' => $data['face_verified'] ?? false,
            'status' => 'present'
        ]);
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

        $attendance->update([
            'clock_out_at' => now(),
            'clock_out_lat' => $data['latitude'],
            'clock_out_lng' => $data['longitude']
        ]);

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
}
