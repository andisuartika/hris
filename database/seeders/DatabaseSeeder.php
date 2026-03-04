<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\OfficeLocation;
use App\Models\Employee;
use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Perusahaan
        $company = Company::create([
            'name' => 'PT Teknologi Kopi Nusantara',
            'timezone' => 'Asia/Jakarta',
        ]);

        // 2. Buat Lokasi Kantor
        $office = OfficeLocation::create([
            'company_id' => $company->id,
            'name' => 'Headquarter',
            'latitude' => -6.200000, // Ganti dengan kordinat asli
            'longitude' => 106.816666,
            'radius_meters' => 100,
        ]);

        // 3. Buat Setting Absensi
        AttendanceSetting::create([
            'company_id' => $company->id,
            'face_match_threshold' => 85,
            'is_gps_enabled' => true,
        ]);

        // 4. Buat User Login
        $user = User::create([
            'name' => 'Budi Kopi',
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 5. Hubungkan User sebagai Employee
        Employee::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'office_location_id' => $office->id,
            'employee_code' => 'EMP-001',
            'full_name' => 'Budi Kopi',
        ]);
    }
}
