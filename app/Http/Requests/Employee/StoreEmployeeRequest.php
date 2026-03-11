<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan return true agar request diizinkan
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_code'      => ['required', 'string', 'unique:employees,employee_code'],
            'full_name'          => ['required', 'string', 'max:255'],
            'email'              => ['required', 'email', 'unique:users,email'],
            'password'           => ['required', 'string', 'min:6', 'confirmed'],
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

    // (Opsional) Custom pesan error jika ingin menggunakan bahasa Indonesia yang lebih luwes
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
