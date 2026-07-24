<?php

namespace App\Services\Auth;

use App\DTO\Auth\LoginDTO;
use App\Models\Employee;
use App\Models\User;
use App\Repositories\Auth\UserRepository;
use App\Services\Sso\KeycloakTokenService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthService
{
    // Mapping role Keycloak -> role Spatie HRIS
    private const ROLE_MAP = [
        'admin'   => 'admin',
        'manager' => 'hr',
        'pegawai' => 'employee',
    ];

    public function __construct(
        protected UserRepository $userRepository,
        protected KeycloakTokenService $keycloak
    ) {}

    // --- API LOGIN (verifikasi kredensial via SSO Keycloak) ---
    public function login(LoginDTO $dto): array
    {
        $claims = $this->keycloak->verifyCredentials($dto->email, $dto->password);

        if (! $claims) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.']
            ]);
        }

        $user = $this->resolveSsoUser($claims);

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Akun SSO valid, tetapi tidak terdaftar sebagai pegawai di HRIS.']
            ]);
        }

        $this->syncRolesFromClaims($user, $claims);

        $token = $this->userRepository->createToken($user, $dto->deviceName);

        $user = $this->getProfile($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Cari user HRIS dari klaim token Keycloak.
     * Urutan: email -> NIP (klaim nip = employees.employee_code).
     */
    private function resolveSsoUser(array $claims): ?User
    {
        if (! empty($claims['email'])) {
            $user = $this->userRepository->findByEmail($claims['email']);
            if ($user) {
                return $user;
            }
        }

        if (! empty($claims['nip'])) {
            return Employee::with('user')
                ->where('employee_code', $claims['nip'])
                ->first()?->user;
        }

        return null;
    }

    private function syncRolesFromClaims(User $user, array $claims): void
    {
        $roles = collect($claims['roles'] ?? [])
            ->map(fn ($r) => self::ROLE_MAP[$r] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($roles !== []) {
            $user->syncRoles($roles);
        }
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
