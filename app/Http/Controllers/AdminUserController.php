<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', 'admin')->paginate(10);
        return view('admin.userModule.userManagement', ['users' => $users]);
    }

    public function edit($userId)
    {
        $user = User::findOrFail($userId);
        return view('admin.userModule.editUser', ['user' => $user]);
    }

    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $name = $request->input('name');
        $email = $request->input('email');
        $role = $request->input('role');

        if (empty($name) || empty($email) || empty($role)) {
            return back()->with('error', 'All fields required');
        }

        $user->name = $name;
        $user->email = $email;
        $user->role = $role;
        $user->save();

        return back()->with('success', 'User updated successfully');
    }

    public function delete($userId)
    {
        $user = User::findOrFail($userId);
        
        if ($user->status === 'active') {
            $user->status = 'banned';
            $message = 'User banned successfully';
        } else {
            $user->status = 'active';
            $message = 'User unbanned successfully';
        }
        
        $user->save();
        
        return back()->with('success', $message);
    }

    public function getUsersJson()
    {
        $users = User::all();
        
        return response()->json([
            'users' => $users->map(function ($user) {
                return [
                    'userId' => $user->userId,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ];
            }),
        ]);
    }
}
