<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Bisa berisi email atau username Keycloak
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string']
        ];
    }
}
