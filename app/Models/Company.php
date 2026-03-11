<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'timezone'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function officeLocations()
    {
        return $this->hasMany(OfficeLocation::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}
