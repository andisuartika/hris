<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Services\Attendance\AttendanceService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ApiAttendanceController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function checkin(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        try {

            $attendance = $this->attendanceService->checkin(
                $request->user(),
                $request->only('latitude', 'longitude')
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
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        try {

            $attendance = $this->attendanceService->checkout(
                $request->user(),
                $request->only('latitude', 'longitude')
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
}



