<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'holiday_date',
        'type',
        'description',
        'is_generated'
    ];

    protected $casts = [
        'holiday_date' => 'date:Y-m-d',
        'is_generated' => 'boolean'
    ];
}
