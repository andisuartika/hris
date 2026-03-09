<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat daftar Permission
        $permissions = [
            // Dashboard
            'dashboard:admin',
            'dashboard:pegawai',

            // Data Master
            'master:perusahaan',
            'master:lokasi',
            'master:departemen',
            'master:jabatan',

            // Kepegawaian
            'pegawai:index',
            'pegawai:create',
            'pegawai:edit',
            'pegawai:delete',
            'pegawai:wajah', // Pendaftaran Wajah
            'pegawai:resign',

            // Absensi
            'absensi:log',
            'absensi:lembur',
            'absensi:shift',
            'absensi:hari-libur',

            // Cuti & Izin
            'cuti:pengajuan',
            'cuti:approval',
            'cuti:saldo',
            'cuti:tipe',

            // Payroll
            'payroll:proses',
            'payroll:komponen',
            'payroll:slip',

            // Lain-lain
            'pengumuman:manage',

            // Sistem
            'setting:absensi',
            'setting:user',
            'setting:role-permission',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // --- 2. DEFINE ROLES & ASSIGN PERMISSIONS ---

        // ADMIN HR (Akses hampir semua menu operasional)
        $hrRole = Role::create(['name' => 'hr']);
        $hrRole->givePermissionTo([
            'dashboard:admin',
            'master:lokasi',
            'master:departemen',
            'master:jabatan',
            'pegawai:index',
            'pegawai:create',
            'pegawai:edit',
            'pegawai:wajah',
            'pegawai:resign',
            'absensi:log',
            'absensi:lembur',
            'absensi:shift',
            'absensi:hari-libur',
            'cuti:pengajuan',
            'cuti:approval',
            'cuti:saldo',
            'payroll:proses',
            'payroll:slip',
            'pengumuman:manage'
        ]);

        // EMPLOYEE (Hanya akses menu mandiri)
        $employeeRole = Role::create(['name' => 'employee']);
        $employeeRole->givePermissionTo([
            'dashboard:pegawai',
            'absensi:log',
            'cuti:pengajuan',
            'payroll:slip'
        ]);

        // SUPER ADMIN (Segalanya)
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());
    }
}
