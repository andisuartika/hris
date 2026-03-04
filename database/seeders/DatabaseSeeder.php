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
        // 1. Panggil Seeder Role & Permission
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // 2. Buat Data Perusahaan
        $company = Company::create([
            'name' => 'Pt Andi Maju Jaya',
            'timezone' => 'Asia/Jakarta',
        ]);

        // 3. Buat Lokasi Kantor
        $office = OfficeLocation::create([
            'company_id' => $company->id,
            'name' => 'Headquarter Office',
            'latitude' => -8.603619788089171,
            'longitude' => 115.17589729052621,
            'radius_meters' => 100,
        ]);

        // 4. Buat Setting Absensi
        AttendanceSetting::create([
            'company_id' => $company->id,
            'face_match_threshold' => 85,
            'is_gps_enabled' => true,
        ]);

        // ==========================================
        // 5. SEEDER UNTUK EMPLOYEE BIASA
        // ==========================================
        $user = User::create([
            'name' => 'Andi Suartika',
            'email' => 'andi@mail.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('employee'); // Berikan role employee
        Employee::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'office_location_id' => $office->id,
            'employee_code' => 'EMP-001',
            'full_name' => 'Andi Suartika',
        ]);

        // ==========================================
        // 6. SEEDER UNTUK HR (TAMBAHAN BARU)
        // ==========================================
        $hrUser = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@mail.com',
            'password' => Hash::make('password123'),
        ]);
        $hrUser->assignRole('hr'); // Berikan role hr
        Employee::create([
            'user_id' => $hrUser->id,
            'company_id' => $company->id,
            'office_location_id' => $office->id,
            'employee_code' => 'HR-001',
            'full_name' => 'HR Manager',
        ]);

        // ==========================================
        // 7. SEEDER UNTUK SUPER ADMIN
        // ==========================================
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole('admin'); // Berikan role admin
    }
}
