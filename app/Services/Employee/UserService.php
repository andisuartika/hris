<?php

namespace App\Services\Employee;

use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserService
{
    public function getAllUsers()
    {
        return User::with(['employee', 'officeLocation', 'department', 'position'])->latest()->get();
    }

    public function getUserDetails($id): User
    {
        return User::with(['employee', 'officeLocation', 'department', 'position'])->findOrFail($id);
    }

    public function getUserById($id): User
    {
        return User::with('employee')->findOrFail($id);
    }


    public function getUsersByRole($role)
    {
        return User::where('role', $role)->with('employee')->get();
    }

    public function createUser(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => $data['status'],
        ]);

        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        return $user;
    }


    public function updatePassword($userId, $newPassword)
    {
        $user = User::findOrFail($userId);
        $user->password = Hash::make($newPassword);
        $user->save();
    }

    public function updateUserProfile($userId, array $data)
    {
        $user = User::findOrFail($userId);
        $user->name = $data['name'] ?? $user->name;
        $user->email = $data['email'] ?? $user->email;
        $user->phone = $data['phone'] ?? $user->phone;
        $user->status = $data['status'] ?? $user->status;

        //update role
        if (isset($data['role'])) {
            $user->syncRoles($data['role']);
        }

        $user->save();
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
    }
}
