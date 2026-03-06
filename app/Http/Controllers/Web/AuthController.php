<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Auth\AuthService;
use App\DTO\Auth\LoginDTO;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Mapping ke DTO (Device name tidak wajib untuk web, bisa dikosongkan)
        $dto = new LoginDTO(
            email: $request->email,
            password: $request->password,
            deviceName: 'Web-Browser'
        );

        $remember = $request->boolean('remember');

        // 3. Eksekusi Service Web Login
        // Jika gagal, ValidationException otomatis dilempar kembali ke view login
        $this->authService->webLogin($dto, $remember);

        // 4. Regenerate session untuk menghindari Session Fixation attack
        $request->session()->regenerate();

        // 5. Redirect sesuai role
        if (auth()->user()->hasRole('admin')) {
            return redirect()->intended('/admin/dashboard');
        }

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        // Eksekusi Service Web Logout
        $this->authService->webLogout();

        return redirect('/login')->with('success', 'Anda telah berhasil logout.');
    }
}
