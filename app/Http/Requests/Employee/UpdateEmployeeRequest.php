<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Employee;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Mengambil ID pegawai dari URL route (misal: /employees/5)
        $employeeId = $this->route('employee');

        // Kita perlu mencari user_id untuk pengecualian validasi email di tabel users
        $employee = Employee::findOrFail($employeeId);

        return [
            'employee_code'      => ['required', 'string', 'unique:employees,employee_code,' . $employeeId],
            'full_name'          => ['required', 'string', 'max:255'],
            'email'              => ['required', 'email', 'unique:users,email,' . $employee->user_id],
            'password'           => ['nullable', 'string', 'min:6', 'confirmed'],
            'phone'              => ['nullable', 'string', 'max:20'],
            'address'            => ['nullable', 'string'],
            'department_id'      => ['nullable', 'exists:departments,id'],
            'position_id'        => ['nullable', 'exists:positions,id'],
            'join_date'          => ['required', 'date'],
            'contract_start'     => ['nullable', 'date'],
            'contract_end'       => ['nullable', 'date', 'after_or_equal:contract_start'],
            'office_locations'   => ['nullable', 'array'],
            'office_locations.*' => ['exists:office_locations,id'],
            'status'             => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_code.unique' => 'NIP / Kode Pegawai ini sudah digunakan oleh pegawai lain.',
            'email.unique'         => 'Alamat email ini sudah terdaftar di sistem.',
            'password.confirmed'   => 'Konfirmasi kata sandi tidak cocok.',
            'contract_end.after_or_equal' => 'Tanggal berakhir kontrak tidak boleh mendahului tanggal mulai kontrak.',
        ];
    }
}
