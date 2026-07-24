<?php

namespace App\Services\Sso;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class KeycloakAdminService
{
    private string $baseUrl;
    private string $realm;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.keycloak.internal_base_url') ?: config('services.keycloak.base_url'), '/');
        $this->realm = config('services.keycloak.realms');
    }

    private function token(): string
    {
        return Cache::remember('keycloak_admin_token', 50, function () {
            $response = Http::asForm()->post("{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/token", [
                'grant_type'    => 'client_credentials',
                'client_id'     => config('services.keycloak.client_id'),
                'client_secret' => config('services.keycloak.client_secret'),
            ]);

            if ($response->failed()) {
                throw new RuntimeException('Gagal mendapatkan token admin Keycloak: '.$response->body());
            }

            return $response->json('access_token');
        });
    }

    private function api()
    {
        return Http::withToken($this->token())
            ->baseUrl("{$this->baseUrl}/admin/realms/{$this->realm}");
    }

    /** @return array<int, array> */
    public function listUsers(int $max = 200): array
    {
        return $this->api()->get('/users', ['max' => $max, 'briefRepresentation' => false])->throw()->json();
    }

    public function getUser(string $id): array
    {
        return $this->api()->get("/users/{$id}")->throw()->json();
    }

    public function findByEmail(string $email): ?array
    {
        $users = $this->api()->get('/users', ['email' => $email, 'exact' => 'true'])->throw()->json();

        return $users[0] ?? null;
    }

    public function createUser(array $data): string
    {
        $response = $this->api()->post('/users', $data);

        if ($response->status() === 409) {
            throw new RuntimeException('User dengan username/email tersebut sudah ada di SSO.');
        }

        $response->throw();

        // Keycloak mengembalikan lokasi user baru di header Location
        return basename($response->header('Location'));
    }

    public function updateUser(string $id, array $data): void
    {
        // PUT Keycloak bersifat replace: merge dengan data existing agar field lain tidak hilang
        $current = $this->getUser($id);
        $this->api()->put("/users/{$id}", array_merge([
            'username'   => $current['username'],
            'email'      => $current['email'] ?? null,
            'firstName'  => $current['firstName'] ?? null,
            'lastName'   => $current['lastName'] ?? null,
            'enabled'    => $current['enabled'] ?? true,
            'attributes' => $current['attributes'] ?? [],
        ], $data))->throw();
    }

    /** Peta user-id => daftar role, untuk role yang dikelola HRIS (3 API call, hindari N+1) */
    public function roleMap(): array
    {
        $map = [];

        foreach (['admin', 'manager', 'pegawai'] as $role) {
            $members = $this->api()->get("/roles/{$role}/users", ['max' => 500])->throw()->json();
            foreach ($members as $member) {
                $map[$member['id']][] = $role;
            }
        }

        return $map;
    }

    public function realmRoles(string $userId): array
    {
        return collect($this->api()->get("/users/{$userId}/role-mappings/realm")->throw()->json())
            ->pluck('name')
            ->all();
    }

    public function syncRealmRoles(string $userId, array $roleNames): void
    {
        $allRoles = collect($this->api()->get('/roles')->throw()->json());

        $assignable = ['admin', 'manager', 'pegawai'];
        $current = $this->api()->get("/users/{$userId}/role-mappings/realm")->throw()->json();

        $toRemove = collect($current)
            ->filter(fn ($r) => in_array($r['name'], $assignable) && ! in_array($r['name'], $roleNames))
            ->values();
        $toAdd = $allRoles
            ->filter(fn ($r) => in_array($r['name'], $roleNames) && ! collect($current)->contains('name', $r['name']))
            ->values();

        if ($toRemove->isNotEmpty()) {
            $this->api()->send('DELETE', "/users/{$userId}/role-mappings/realm", ['json' => $toRemove->all()])->throw();
        }

        if ($toAdd->isNotEmpty()) {
            $this->api()->post("/users/{$userId}/role-mappings/realm", $toAdd->all())->throw();
        }
    }
}
