<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkScheduleDay extends Model
{
    protected $fillable = [
        'work_schedule_id',
        'day_of_week',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'work_hours',
        'tolerance_late',
        'tolerance_early_leave',
        'is_working_day',
    ];

    protected $casts = [
        'is_working_day' => 'boolean'
    ];

    public function schedule()
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }
}
