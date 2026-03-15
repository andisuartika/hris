<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use App\Services\Attendance\WorkScheduleService;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    protected $service;

    public function __construct(WorkScheduleService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $schedules = $this->service->getAll();

        return view('pages.work-schedules.index', compact('schedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:work_schedules'
        ]);

        $this->service->store($request->all());

        return redirect()
            ->route('work-schedules.index')
            ->with('success', 'Work schedule created');
    }

    public function update(Request $request, WorkSchedule $workSchedule)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required'
        ]);


        $this->service->update($workSchedule, $request->all());

        return redirect()
            ->route('work-schedules.index')
            ->with('success', 'Work schedule updated');
    }

    public function destroy(WorkSchedule $workSchedule)
    {
        $this->service->delete($workSchedule);

        return redirect()
            ->route('work-schedules.index')
            ->with('success', 'Work schedule deleted');
    }
}
