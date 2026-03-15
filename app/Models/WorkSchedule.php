<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'break_duration',
        'work_hours',
        'tolerance_late',
        'tolerance_early_leave',
        'is_flexible',
        'is_default',
        'is_active'
    ];

    public function days()
    {
        return $this->hasMany(WorkScheduleDay::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
