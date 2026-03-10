<?php

namespace App\Services\Master;



use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function getAllRoles()
    {
        return Role::with('permissions')->get();
    }

    public function createRole(array $data)
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web'
            ]);

            if (isset($data['permissions'])) {
                $role->givePermissionTo($data['permissions']);
            }

            return $role;
        });
    }

    public function updateRole($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $role = \Spatie\Permission\Models\Role::findOrFail($id);
            $role->update(['name' => $data['name']]);
            $role->syncPermissions($data['permissions'] ?? []);

            return $role;
        });
    }

    public function deleteRole(Role $role)
    {
        return DB::transaction(function () use ($role) {
            // Pastikan role bukan 'admin' super yang tidak boleh dihapus
            if ($role->name === 'admin') {
                throw new \Exception("Role Administrator utama tidak dapat dihapus.");
            }
            return $role->delete();
        });
    }
}
