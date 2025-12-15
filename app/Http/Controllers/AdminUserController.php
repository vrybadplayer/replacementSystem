<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display users list
     */
    public function index()
    {
        $users = User::where('role', '!=', 'admin')->paginate(10);
        return view('admin.userModule.userManagement', ['users' => $users]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.userModule.addUser');
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'role' => 'required|string|in:student,lecturer',
        ]);

        // Generate userId using UserFactory's pattern
        $lastUser = User::orderBy('userId', 'desc')->first();
        $nextNumber = 1;
        if ($lastUser && preg_match('/U(\d+)/', $lastUser->userId, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        }
        $validated['userId'] = 'U' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Use UserFactory to create user with all defaults
        User::factory()->create($validated);
        
        return redirect()->route('admin.user.index')->with('success', 'User created successfully');
    }

    /**
     * Show user details
     */
    public function show($userId)
    {
        $user = $this->userService->getUserById($userId);
        
        if (!$user) {
            abort(404);
        }
        
        return view('admin.userModule.showUser', ['user' => $user]);
    }

    /**
     * Show edit form
     */
    public function edit($userId)
    {
        $user = $this->userService->getUserById($userId);
        
        if (!$user) {
            abort(404);
        }
        
        return view('admin.userModule.editUser', ['user' => $user]);
    }

    /**
     * Update user
     */
    public function update(Request $request, $userId)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $role = $request->input('role');

        if (empty($name) || empty($email) || empty($role)) {
            return back()->with('error', 'All fields required');
        }

        if ($role === 'admin') {
            return back()->with('error', 'Cannot change user role to admin');
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'role' => $role,
        ];

        $this->userService->updateUser($userId, $data);
        return redirect()->route('admin.user.index')->with('success', 'User updated successfully');
    }

    /**
     * Delete or ban user
     */
    public function delete($userId)
    {
        $user = $this->userService->getUserById($userId);

        if (!$user) {
            abort(404);
        }
        
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

    /**
     * Get users as JSON
     */
    public function getUsersJson()
    {
        $users = $this->userService->getAllUsersFromDb();
        
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
