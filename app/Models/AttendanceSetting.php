<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    public const MODE_TAP = 'tap';
    public const MODE_LOCATION = 'location';
    public const MODE_LOCATION_FACE = 'location_face';

    protected $fillable = [
        'company_id',
        'verification_mode',
        'face_match_threshold',
        'is_gps_enabled',
    ];

    protected $casts = [
        'is_gps_enabled' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function forCompany(?int $companyId): self
    {
        return self::firstOrCreate(
            ['company_id' => $companyId],
            ['verification_mode' => self::MODE_LOCATION]
        );
    }

    public function requiresLocation(): bool
    {
        return in_array($this->verification_mode, [self::MODE_LOCATION, self::MODE_LOCATION_FACE]);
    }

    public function requiresFace(): bool
    {
        return $this->verification_mode === self::MODE_LOCATION_FACE;
    }
}
