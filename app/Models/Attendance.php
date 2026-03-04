<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['employee_id', 'work_date', 'clock_in_at', 'clock_out_at', 'status'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
