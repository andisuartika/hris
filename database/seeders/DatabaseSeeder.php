<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\OfficeLocation;
use App\Models\Employee;
use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. DATA MASTER & ROLE (Harus paling awal)
        // ==========================================
        $this->call([
            RolePermissionSeeder::class,
            MasterDataSeeder::class, // Membuat Company, Department, dan Position
        ]);

        // ==========================================
        // 2. BUAT LOKASI KANTOR & SETTING
        // ==========================================
        // Kita asumsikan Company ID 1 sudah dibuat oleh MasterDataSeeder
        $office = OfficeLocation::create([
            'company_id' => 1,
            'name' => 'Headquarter Office',
            'latitude' => -8.603619788089171,
            'longitude' => 115.17589729052621,
            'radius_meters' => 100,
        ]);

        AttendanceSetting::create([
            'company_id' => 1,
            'face_match_threshold' => 85,
            'is_gps_enabled' => true,
        ]);

        // ==========================================
        // 3. SEEDER MANUAL (Admin, HR, dan Andi)
        // ==========================================

        // --- Super Admin ---
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole('admin');

        // --- HR Manager ---
        $hrUser = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@mail.com',
            'password' => Hash::make('password123'),
        ]);
        $hrUser->assignRole('hr');
        Employee::create([
            'user_id' => $hrUser->id,
            'company_id' => 1,
            'office_location_id' => $office->id,
            'office_locations' => [$office->id], // Format array JSON untuk kolom baru
            'employee_code' => 'HR-001',
            'full_name' => 'HR Manager',
            'join_date' => Carbon::now(), // Isi kolom tanggal agar tidak error
        ]);

        // --- Employee Biasa (Andi) ---
        $user = User::create([
            'name' => 'Andi Suartika',
            'email' => 'andi@mail.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('employee');
        Employee::create([
            'user_id' => $user->id,
            'company_id' => 1,
            'office_location_id' => $office->id,
            'office_locations' => [$office->id], // Format array JSON untuk kolom baru
            'employee_code' => 'EMP-001',
            'full_name' => 'Andi Suartika',
            'join_date' => Carbon::now(),
        ]);

        // ==========================================
        // 4. GENERATE 50 DATA DUMMY
        // ==========================================
        $this->call([
            EmployeeSeeder::class,
        ]);
    }
}
