<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Employee\UserService;
use Illuminate\Support\Facades\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index()
    {
        $users = $this->userService->getAllUsers();
        $roles = Role::all();
        return view('pages.users.index', compact('users', 'roles'));
    }

    public function show($id)
    {
        $user = $this->userService->getUserById($id);
        return view('pages.users.detail', compact('user'));
    }

    public function store()
    {
        // Implementasi penyimpanan user baru
        $data = request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'status' => 'required',
        ]);

        $this->userService->createUser($data);
        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(User $user)
    {
        $data = request()->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'role'     => 'required',
            'password' => 'nullable|min:6|confirmed',
        ]);

        if (request()->filled('password')) {
            $this->userService->updatePassword($user->id, $data['password']);
        }

        $this->userService->updateUserProfile($user->id, $data);
        $user->syncRoles($data['role']);

        return back()->with('success', 'User updated successfully');
    }
}
