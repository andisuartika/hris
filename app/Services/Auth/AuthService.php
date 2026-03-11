<?php

namespace App\Services\Auth;

use App\DTO\Auth\LoginDTO;
use App\Models\User;
use App\Repositories\Auth\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    // --- API LOGIN ---
    public function login(LoginDTO $dto): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.']
            ]);
        }

        $token = $this->userRepository->createToken($user, $dto->deviceName);

        $user = $this->getProfile($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $this->userRepository->revokeCurrentToken($user);
    }

    public function getProfile(User $user): User
    {
        return $user->load([
            'employee.department',
            'employee.position',
            'employee.officeLocation'
        ]);
    }

    // --- WEB LOGIN ---
    public function webLogin(LoginDTO $dto, bool $remember = false): void
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.']
            ]);
        }

        Auth::login($user, $remember);
    }

    public function webLogout(): void
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
    }
}
