<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SsoController extends Controller
{
    // Mapping role Keycloak -> role Spatie di HRIS
    private const ROLE_MAP = [
        'admin'   => 'admin',
        'manager' => 'hr',
        'pegawai' => 'employee',
    ];

    public function __construct(
        protected AuthService $authService
    ) {}

    public function redirect()
    {
        return Socialite::driver('keycloak')->redirect();
    }

    public function callback(Request $request)
    {
        $socialUser = Socialite::driver('keycloak')->user();

        $roles = collect($socialUser->getRaw()['roles'] ?? [])
            ->map(fn ($r) => self::ROLE_MAP[$r] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $user = $this->resolveUser($socialUser);

        $user->update(['keycloak_id' => $socialUser->getId()]);

        if ($roles !== []) {
            $user->syncRoles($roles);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('keycloak_id_token', $socialUser->accessTokenResponseBody['id_token'] ?? null);

        // Satu-satunya dashboard saat ini adalah /admin/dashboard (dibatasi middleware auth saja)
        return redirect()->intended('/admin/dashboard');
    }

    // Setiap pegawai HRIS selalu punya user (employees.user_id NOT NULL), jadi
    // penautan SSO = menemukan user pegawai yang sudah ada, bukan membuat tautan baru.
    // Urutan: keycloak_id -> email -> NIP (klaim nip = employee_code) -> buat user baru.
    private function resolveUser($socialUser): User
    {
        $user = User::where('keycloak_id', $socialUser->getId())
            ->orWhere('email', $socialUser->getEmail())
            ->first();

        if ($user) {
            return $user;
        }

        $nip = $socialUser->getRaw()['nip'] ?? null;

        if ($nip) {
            $employee = Employee::with('user')->where('employee_code', $nip)->first();

            if ($employee?->user) {
                return $employee->user;
            }
        }

        return User::create([
            'name'     => $socialUser->getName() ?? $socialUser->getNickname(),
            'email'    => $socialUser->getEmail(),
            'password' => Str::random(40),
        ]);
    }

    public function logout(Request $request)
    {
        $idToken = $request->session()->get('keycloak_id_token');

        $this->authService->webLogout();

        $logoutUrl = Socialite::driver('keycloak')->getLogoutUrl(
            url('/'),
            config('services.keycloak.client_id'),
            $idToken
        );

        return redirect($logoutUrl);
    }
}
