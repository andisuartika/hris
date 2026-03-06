<?php

namespace App\Services\Auth;

use App\DTO\Auth\LoginDTO;
use App\Repositories\Auth\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    // --- UNTUK API ---
    public function login(LoginDTO $dto): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.']
            ]);
        }

        // Generate token untuk API
        $token = $this->userRepository->createToken($user, $dto->deviceName);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout($user): void
    {
        $this->userRepository->revokeCurrentToken($user);
    }

    // --- UNTUK WEB ---
    public function webLogin(LoginDTO $dto, bool $remember = false): void
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.']
            ]);
        }

        // Buat session login bawaan Laravel
        Auth::login($user, $remember);
    }

    public function webLogout(): void
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
    }
}
