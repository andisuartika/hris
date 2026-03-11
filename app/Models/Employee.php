<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'office_location_id',
        'employee_code',
        'full_name',
        'email',
        'phone',
        'address',
        'status',
        'department_id',
        'position_id',
        'join_date',
        'contract_start',
        'contract_end',
        'office_locations'
    ];

    protected $casts = [
        'join_date' => 'date',
        'contract_start' => 'date',
        'contract_end' => 'date',
        'office_locations' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function officeLocation()
    {
        return $this->belongsTo(OfficeLocation::class);
    }
    public function faceTemplate()
    {
        return $this->hasOne(EmployeeFaceTemplate::class);
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
