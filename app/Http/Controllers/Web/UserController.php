<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Services\Sso\KeycloakAdminService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        protected KeycloakAdminService $keycloak
    ) {}

    public function index()
    {
        $ssoUsers = collect($this->keycloak->listUsers())
            ->reject(fn ($u) => str_starts_with($u['username'], 'service-account-'));
        $roleMap = $this->keycloak->roleMap();

        $localUsers = User::with('employee')
            ->whereIn('keycloak_id', $ssoUsers->pluck('id'))
            ->orWhereIn('email', $ssoUsers->pluck('email')->filter())
            ->get();

        $employeesByCode = Employee::with('user')->get()->keyBy('employee_code');

        $users = $ssoUsers->map(function ($u) use ($roleMap, $localUsers, $employeesByCode) {
            $nip = $u['attributes']['nip'][0] ?? null;

            $local = $localUsers->first(fn ($l) => $l->keycloak_id === $u['id'])
                ?? $localUsers->first(fn ($l) => $l->email === ($u['email'] ?? null));

            $employee = $local?->employee ?? ($nip ? $employeesByCode->get($nip) : null);

            return (object) [
                'id'       => $u['id'],
                'username' => $u['username'],
                'name'     => trim(($u['firstName'] ?? '').' '.($u['lastName'] ?? '')) ?: $u['username'],
                'email'    => $u['email'] ?? null,
                'enabled'  => $u['enabled'] ?? true,
                'nip'      => $nip,
                'roles'    => $roleMap[$u['id']] ?? [],
                'employee' => $employee,
            ];
        })->values();

        $employees = Employee::orderBy('full_name')->get(['id', 'employee_code', 'full_name']);
        $roles = ['admin', 'manager', 'pegawai'];

        return view('pages.users.index', compact('users', 'roles', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'username'    => 'required|string|max:100',
            'email'       => 'required|email|max:255',
            'role'        => ['required', Rule::in(['admin', 'manager', 'pegawai'])],
            'employee_id' => 'nullable|exists:employees,id',
            'password'    => 'required|string|min:6|confirmed',
        ]);

        [$firstName, $lastName] = array_pad(explode(' ', $data['name'], 2), 2, '');
        $employee = ! empty($data['employee_id']) ? Employee::find($data['employee_id']) : null;

        $keycloakId = $this->keycloak->createUser([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'firstName'     => $firstName,
            'lastName'      => $lastName,
            'enabled'       => true,
            'emailVerified' => true,
            'attributes'    => $employee ? ['nip' => [$employee->employee_code]] : [],
            'credentials'   => [[
                'type'      => 'password',
                'value'     => $data['password'],
                'temporary' => true,
            ]],
        ]);

        $this->keycloak->syncRealmRoles($keycloakId, [$data['role']]);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dibuat di SSO. Password bersifat sementara dan wajib diganti saat login pertama.');
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'role'        => ['required', Rule::in(['admin', 'manager', 'pegawai'])],
            'employee_id' => 'nullable|exists:employees,id',
            'enabled'     => 'required|in:1,0',
        ]);

        [$firstName, $lastName] = array_pad(explode(' ', $data['name'], 2), 2, '');
        $employee = ! empty($data['employee_id']) ? Employee::find($data['employee_id']) : null;

        $this->keycloak->updateUser($id, [
            'email'      => $data['email'],
            'firstName'  => $firstName,
            'lastName'   => $lastName,
            'enabled'    => $data['enabled'] === '1',
            'attributes' => $employee ? ['nip' => [$employee->employee_code]] : [],
        ]);

        $this->keycloak->syncRealmRoles($id, [$data['role']]);

        // Sinkronkan shadow user lokal jika sudah pernah login
        User::where('keycloak_id', $id)->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        return back()->with('success', 'Pengguna SSO berhasil diperbarui.');
    }
}
