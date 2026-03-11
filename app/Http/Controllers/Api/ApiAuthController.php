<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\DTO\Auth\LoginDTO;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\ProfileResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ApiAuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Login
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
