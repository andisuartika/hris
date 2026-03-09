<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\OfficeLocation;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Menggunakan lokalisasi Indonesia agar nama, alamat, dan no HP terlihat lokal
        $faker = Faker::create('id_ID');

        // Ambil semua data referensi utama
        $company = Company::first();
        $offices = OfficeLocation::all();
        $departments = Department::all();
        $positions = Position::all();

        // Pengecekan agar seeder tidak error jika data master kosong
        if (!$company || $offices->isEmpty() || $departments->isEmpty() || $positions->isEmpty()) {
            $this->command->error('Data Master (Perusahaan, Lokasi, Departemen, atau Jabatan) belum lengkap. Jalankan MasterDataSeeder terlebih dahulu.');
            return;
        }

        $this->command->info('Mulai membuat 50 data pegawai dummy lengkap...');

        for ($i = 1; $i <= 50; $i++) {
            // 1. Buat Data User (Untuk Login)
            $user = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password123'),
            ]);

            // 2. Berikan Hak Akses (Role) Employee
            $user->assignRole('employee');

            // 3. Generate Kode Pegawai (Contoh: EMP-2603-0042)
            $employeeCode = 'EMP-' . date('ym') . '-' . str_pad($faker->unique()->numberBetween(10, 9999), 4, '0', STR_PAD_LEFT);

            // 4. Buat Tanggal Acak Logis (Join Date, Contract Start, Contract End)
            $joinDate = $faker->dateTimeBetween('-5 years', 'now');
            $contractStart = clone $joinDate;
            // Kontrak ditambah acak 1 atau 2 tahun dari tanggal mulai
            $contractEnd = (clone $contractStart)->modify('+' . $faker->randomElement([1, 2]) . ' years');

            // 5. Pilih acak 1 atau 2 Lokasi Kantor untuk array multi-select
            // Jika kantormu di database baru 1, dia otomatis hanya pilih 1
            $randomOffices = $offices->random(rand(1, min(2, $offices->count())))->pluck('id')->toArray();

            // 6. Buat Profil Pegawai Lengkap
            Employee::create([
                'user_id'            => $user->id,
                'company_id'         => $company->id,
                'employee_code'      => $employeeCode,
                'full_name'          => $user->name,

                // Distribusi probabilitas: lebih banyak yang aktif (75% aktif, 25% non-aktif)
                'status'             => $faker->randomElement(['active', 'active', 'active', 'inactive']),

                // Data Kolom Baru
                'address'            => $faker->address,
                'phone'              => $faker->phoneNumber,
                'department_id'      => $departments->random()->id,
                'position_id'        => $positions->random()->id,
                'join_date'          => $joinDate,
                'contract_start'     => $contractStart,
                'contract_end'       => $contractEnd,
                'office_locations'   => $randomOffices,

                // Tetap isi id lokasi utama untuk jaga-jaga jika ada relasi lama yang belum dihapus
                'office_location_id' => $randomOffices[0],
            ]);
        }

        $this->command->info('50 data pegawai (lengkap dengan departemen & multi-lokasi) berhasil ditambahkan!');
    }
}
