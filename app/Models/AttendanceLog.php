<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'attendance_id',
        'type',
        'timestamp',
        'latitude',
        'longitude',
        'is_in_radius',
        'face_similarity_score',
        'photo_path',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'is_in_radius' => 'boolean',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
