<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeFaceTemplate extends Model
{
    protected $fillable = [
        'employee_id',
        'template_data',
        'image_path',
    ];

    protected $casts = [
        'template_data' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
