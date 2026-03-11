<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['company_id', 'name'];

    // Relasi dengan Company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
