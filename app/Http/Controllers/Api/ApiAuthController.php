<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\DTO\Auth\LoginDTO;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\ProfileResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @group Autentikasi
 *
 * Login SSO, profil, dan logout.
 */
class ApiAuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Login
     *
     * Login via SSO Keycloak. Field `email` dapat diisi email atau username SSO.
     * @unauthenticated
     */
    public function login(LoginRequest $request)
    {
        try {
            $dto = LoginDTO::fromRequest($request);

            $result = $this->authService->login($dto);

            // pakai trait success()
            return $this->success(
                data: [
                    'user' => $result['user'],
                    'token' => $result['token']
                ],
                message: 'Login berhasil'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            // pakai trait validationError()
            return $this->validationError(
                errors: $e->errors(),
                message: 'Email atau password salah'
            );
        } catch (\Throwable $e) {
            // pakai trait error() untuk general exception
            return $this->error(
                errors: $e->getMessage(),
                message: 'Login gagal',
                status: 500
            );
        }
    }

    /**
     * Get Profile
     */
    public function profile()
    {
        $user = $this->authService->getProfile(auth()->user());
        return $this->success(
            new ProfileResource($user),
            'User profile'
        );
    }
    /**
     * Refresh Token
     *
     * Tukar token lama dengan token baru (token lama dicabut). Panggil sebelum token
     * kedaluwarsa (30 hari) agar user tidak perlu login ulang.
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $deviceName = $user->currentAccessToken()->name;

        $user->currentAccessToken()->delete();
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->success(['token' => $token], 'Token diperbarui');
    }

    /**
     * Update Profile (phone, alamat, foto).
     * Data identitas lain (nama, email, password) dikelola via SSO/admin.
     */
    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'photo'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->error(
                errors: 'Akun tidak memiliki data pegawai.',
                message: 'Data pegawai tidak ditemukan',
                status: 404
            );
        }

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $data['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $employee->update(array_filter($data, fn ($v) => $v !== null));

        $user = $this->authService->getProfile($request->user()->fresh());

        return $this->success(
            new ProfileResource($user),
            'Profil berhasil diperbarui'
        );
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());

            return $this->success(message: 'Logout berhasil');
        } catch (\Throwable $e) {
            return $this->error(
                errors: $e->getMessage(),
                message: 'Logout gagal',
                status: 500
            );
        }
    }
}
