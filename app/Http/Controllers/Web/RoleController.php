<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Master\RoleService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $roles = $this->roleService->getAllRoles();

        $permissions = Permission::all();

        $permissions = \Spatie\Permission\Models\Permission::all()->groupBy(function ($perm) {
            return explode(':', $perm->name)[0];
        });


        return view('pages.master.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array'
        ]);

        try {
            $this->roleService->createRole($data);
            return back()->with('success', 'Role berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambah role: ' . $e->getMessage());
        }
    }

    // app/Http/Controllers/Master/RoleController.php

    public function update(Request $request, $id) // Gunakan $id jika binding gagal
    {
        $request->validate([
            'name' => 'required|string',
            'permissions' => 'nullable|array'
        ]);

        try {
            // Cari menggunakan Spatie Role secara eksplisit jika Model Extend bermasalah
            $role = \Spatie\Permission\Models\Role::findOrFail($id);

            $role->update(['name' => $request->name]);
            $role->syncPermissions($request->permissions ?? []);

            return back()->with('success', 'Role berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui role: ' . $e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        try {
            $this->roleService->deleteRole($role);
            return back()->with('success', 'Role berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
