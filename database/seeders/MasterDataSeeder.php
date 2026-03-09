<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Mulai melakukan seeding Data Master...');

        // 1. Buat Data Perusahaan (Company)
        // Sesuaikan nama kolom jika di tabel companies kamu ada kolom lain seperti 'address', 'email', dll.
        $company = Company::create([
            'name' => 'PT. Andi Technology Solutions',
            'timezone' => 'Asia/Jakarta',
        ]);

        $this->command->info('=> Perusahaan berhasil dibuat: ' . $company->name);

        // 2. Buat Data Departemen
        $departments = [
            'IT & Engineering',
            'Human Resources (HR)',
            'Finance & Accounting',
            'Operations',
            'Marketing & Public Relations',
            'Customer Service'
        ];

        foreach ($departments as $deptName) {
            Department::create([
                'company_id' => $company->id,
                'name'       => $deptName,
            ]);
        }

        $this->command->info('=> ' . count($departments) . ' Data Departemen berhasil ditambahkan.');

        // 3. Buat Data Jabatan (Position)
        $positions = [
            'Direktur Utama',
            'Manajer',
            'Supervisor',
            'Senior Staff',
            'Junior Staff',
            'Intern / Magang'
        ];

        foreach ($positions as $posName) {
            Position::create([
                'company_id' => $company->id,
                'name'       => $posName,
            ]);
        }

        $this->command->info('=> ' . count($positions) . ' Data Jabatan berhasil ditambahkan.');
        $this->command->info('Seeding Data Master selesai!');
    }
}
