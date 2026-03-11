<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeLocation extends Model
{
    protected $fillable = ['company_id', 'name', 'latitude', 'longitude', 'radius_meters'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
