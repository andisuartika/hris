<?php

namespace App\Services\Employee;

use App\Models\User;
use App\Models\Employee;
use App\DTO\Employee\EmployeeDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function getAllEmployees()
    {
        return Employee::with(['user', 'officeLocation', 'department', 'position'])->latest()->get();
    }

    public function getAllEmployeesByCompany(int $companyId, array $filters = [])
    {
        // 1. Inisiasi Query dengan Eager Loading
        $query = Employee::with(['user', 'officeLocation', 'department', 'position'])
            ->where('company_id', $companyId);


        // 2. Terapkan Filter Pencarian (nama, email, kode) jika ada
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('full_name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('employee_code', 'like', "%{$searchTerm}%");
            });
        }

        // 3. Terapkan Filter Lokasi (jika ada)
        if (!empty($filters['location_id'])) {
            $query->where('office_location_id', $filters['location_id']);
        }

        // 4. Terapkan Filter Status (jika ada)
        if (!empty($filters['status'])) {
            // Perbaikan: Langsung passing string 'active' atau 'inactive'
            $query->where('status', $filters['status']);
        }

        // 5. Eksekusi Query
        return $query->latest()->get();
    }


    public function getEmployeeDetails($id): Employee
    {
        return Employee::with(['user', 'officeLocation', 'department', 'position'])->findOrFail($id);
    }

    public function getEmployeeById($id): Employee
    {
        return Employee::with('user')->findOrFail($id);
    }

    public function createEmployee(EmployeeDTO $dto): void
    {
        // Gunakan DB Transaction agar jika gagal di tabel employee, data user ikut terhapus
        DB::transaction(function () use ($dto) {
            // 1. Buat Akun User untuk Login
            $user = User::create([
                'name' => $dto->fullName,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'password' => Hash::make($dto->password),
            ]);

            // 2. Berikan Role default 'employee' (Spatie Permission)
            $user->assignRole('employee');

            $primaryLocationId = !empty($dto->officeLocations) ? $dto->officeLocations[0] : null;

            // 3. Buat Data Profil Pegawai
            Employee::create([
                'user_id' => $user->id,
                'company_id' => $dto->companyId,
                'office_location_id' => $primaryLocationId,
                'employee_code' => $dto->employeeCode,
                'full_name' => $dto->fullName,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'address' => $dto->address,
                'status' => $dto->status,
                'department_id' => $dto->departmentId,
                'position_id' => $dto->positionId,
                'join_date' => $dto->joinDate,
                'contract_start' => $dto->contractStart,
                'contract_end' => $dto->contractEnd,
                'office_locations' => $dto->officeLocations
            ]);
        });
    }

    public function updateEmployee($id, EmployeeDTO $dto): void
    {
        DB::transaction(function () use ($id, $dto) {
            $employee = Employee::findOrFail($id);
            $user = $employee->user;

            // 1. Update Data User Login
            $userData = [
                'name' => $dto->fullName,
                'email' => $dto->email,
                'phone' => $dto->phone,
            ];

            // Jika password diisi di form, maka update passwordnya
            if (!empty($dto->password)) {
                $userData['password'] = Hash::make($dto->password);
            }
            $user->update($userData);

            $primaryLocationId = !empty($dto->officeLocations) ? $dto->officeLocations[0] : null;

            // 2. Update Data Profil Pegawai
            $employee->update([
                'office_location_id' => $primaryLocationId,
                'employee_code' => $dto->employeeCode,
                'full_name' => $dto->fullName,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'address' => $dto->address,
                'status' => $dto->status,
                'department_id' => $dto->departmentId,
                'position_id' => $dto->positionId,
                'join_date' => $dto->joinDate,
                'contract_start' => $dto->contractStart,
                'contract_end' => $dto->contractEnd,
                'office_locations' => $dto->officeLocations,
            ]);
        });
    }

    public function deleteEmployee($id): void
    {
        DB::transaction(function () use ($id) {
            $employee = Employee::findOrFail($id);

            // Hapus User-nya, karena berkat Cascade / SoftDelete,
            // data Employee akan otomatis terhapus atau disembunyikan.
            if ($employee->user) {
                $employee->user->delete();
            }

            $employee->delete();
        });
    }
}
