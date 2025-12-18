<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Facades\CourseSubjectManagementFacade;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
        try {
            // Query database directly for list view with pagination
            $users = User::where('role', '!=', 'admin')->paginate(10);
            
            return view('admin.userModule.userManagement', ['users' => $users]);
        } catch (\Exception $e) {
            \Log::error('Error in AdminUserController::index: ' . $e->getMessage());
            return view('admin.userModule.userManagement', ['users' => collect([]), 'error' => 'Unable to load users']);
        }
    }

    /**
     * Get filtered users for AJAX requests
     */
    public function getFilteredUsers(Request $request)
    {
        try {
            $query = User::where('role', '!=', 'admin');

            // Filter by role
            if ($request->has('role') && !empty($request->input('role'))) {
                $query->where('role', $request->input('role'));
            }

            // Filter by status
            if ($request->has('status') && !empty($request->input('status'))) {
                $query->where('status', $request->input('status'));
            }

            // Search by name or email
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('userId', 'like', "%{$search}%");
                });
            }

            // Paginate
            $page = $request->input('page', 1);
            $users = $query->paginate(10, ['*'], 'page', $page);

            // Return HTML table rows
            $html = '';
            if ($users->count() > 0) {
                foreach ($users as $user) {
                    $statusBadge = $user->status;
                    $roleBadge = $user->role;
                    $confirmMessage = $user->status === 'active' ? 'Are you sure you want to ban this user?' : 'Are you sure you want to unban this user?';
                    $actionLabel = $user->status === 'active' ? 'Ban' : 'Unban';
                    
                    $html .= '<tr class="user-row" data-user-id="' . $user->userId . '">
                        <td style="width: 30px;"><input type="checkbox" class="user-checkbox" data-user-id="' . $user->userId . '" style="cursor: pointer;"></td>
                        <td><strong>' . $user->userId . '</strong></td>
                        <td>' . $user->name . '</td>
                        <td>' . $user->email . '</td>
                        <td>
                            <span class="role-badge role-' . $roleBadge . '">
                                ' . ucfirst($roleBadge) . '
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-' . $statusBadge . '">
                                ' . ucfirst($user->status) . '
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="' . route('admin.user.show', $user->userId) . '" class="btn-view">View</a>
                                <a href="' . route('admin.user.edit', $user->userId) . '" class="btn-edit">Edit</a>
                                <form method="POST" action="' . route('admin.user.delete', $user->userId) . '" style="display: inline;" onsubmit="return confirm(\'' . $confirmMessage . '\');">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn-delete">
                                        ' . $actionLabel . '
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>';
                }
            } else {
                $html = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">No users found.</td></tr>';
            }

            // Get pagination links
            $paginationHtml = $users->links('pagination::bootstrap-4')->render();

            return response()->json([
                'html' => $html,
                'pagination' => $paginationHtml,
                'count' => $users->total(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in AdminUserController::getFilteredUsers: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to load users',
                'html' => '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #d32f2f;">Error loading users</td></tr>'
            ], 500);
        }
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
            'email' => 'required|email',
            'role' => 'required|string|in:student,lecturer',
        ]);

        // Generate userId
        $allUsers = $this->userService->getAllUsersFromDb();
        $nextNumber = 1;
        if (!empty($allUsers)) {
            $lastUserId = max(array_map(function ($user) {
                if (isset($user['userId']) && preg_match('/U(\d+)/', $user['userId'], $matches)) {
                    return (int)$matches[1];
                }
                return 0;
            }, is_array($allUsers) ? $allUsers : [$allUsers]));
            $nextNumber = $lastUserId + 1;
        }
        $validated['userId'] = 'U' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $validated['status'] = 'active';
        
        // Create user using factory based on role
        $role = $validated['role'];
        if ($role === 'lecturer') {
            $user = User::factory()->lecturer()->create($validated);
        } else {
            $user = User::factory()->student()->create($validated);
        }
        
        // Log user creation by admin
        \Log::info('User created by admin', [
            'created_user_id' => $validated['userId'],
            'created_user_email' => $validated['email'],
            'created_user_role' => $validated['role'],
            'admin_user_id' => auth()->user()->userId,
            'admin_email' => auth()->user()->email,
            'timestamp' => now()
        ]);
        
        // Send email with credentials
        try {
            Mail::send('emails.user-created', [
                'userName' => $validated['name'],
                'userEmail' => $validated['email'],
                'userId' => $validated['userId'],
                'password' => '123456',
            ], function ($message) use ($validated) {
                $message->to($validated['email'])->subject('Your Account Has Been Created');
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send user creation email: ' . $e->getMessage());
        }
        
        return redirect()->route('admin.user.index')->with('success', 'User created successfully. Login credentials have been sent to ' . $validated['email']);
    }

    /**
     * Show user details
     */
    public function show($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            abort(404);
        }

        $enrolledCourses = $this->userService->getEnrolledCourses($userId);
        
        // Get all course subjects from DATABASE with relationships
        $subjects = [];
        if (!empty($enrolledCourses)) {
            try {
                $courseIds = array_column($enrolledCourses, 'courseID');
                
                \Log::info('Admin viewing user details - fetching subjects', [
                    'admin_user_id' => auth()->user()->userId,
                    'viewed_user_id' => $userId,
                    'course_ids' => $courseIds
                ]);
                
                // Get all course subjects from DATABASE with relationships
                $courseSubjects = \App\Models\CourseSubject::with(['course', 'subject'])
                    ->whereIn('courseId', $courseIds)
                    ->get();
                
                \Log::info('Retrieved course subjects from database', [
                    'count' => count($courseSubjects)
                ]);
                
                // Build subjects list
                foreach ($courseSubjects as $courseSubject) {
                    if ($courseSubject->subject) {
                        $subject = $courseSubject->subject;
                        $course = $courseSubject->course;
                        
                        $subjects[] = [
                            'subjectId' => $subject->subjectId,
                            'subjectCode' => $subject->subjectCode,
                            'subjectName' => $subject->subjectName,
                            'creditHours' => $subject->creditHours,
                            'category' => $subject->category,
                            'courseName' => $course->courseName,
                            'courseCode' => $course->courseCode,
                        ];
                    }
                }
                
                \Log::info('Admin user details - subjects prepared', [
                    'viewed_user_id' => $userId,
                    'subjects_count' => count($subjects)
                ]);
            } catch (\Exception $e) {
                \Log::error('Error fetching subjects in admin show', [
                    'error' => $e->getMessage(),
                    'viewed_user_id' => $userId
                ]);
            }
        }
        
        return view('admin.userModule.showUser', ['user' => $user, 'enrolledCourses' => $enrolledCourses, 'subjects' => $subjects]);
    }

    /**
     * Show edit form
     */
    public function edit($userId)
    {
        $user = User::find($userId);
        
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
        try {
            $user = User::find($userId);

            if (!$user) {
                abort(404);
            }
            
            $currentStatus = $user->status ?? 'active';
            $newStatus = $currentStatus === 'active' ? 'banned' : 'active';
            $message = $newStatus === 'banned' ? 'User banned successfully' : 'User unbanned successfully';
            
            // Log user status change
            $action = $newStatus === 'banned' ? 'banned' : 'unbanned';
            \Log::warning('User status changed by admin', [
                'action' => $action,
                'affected_user_id' => $userId,
                'affected_user_email' => $user->email,
                'previous_status' => $currentStatus,
                'new_status' => $newStatus,
                'admin_user_id' => auth()->user()->userId,
                'admin_email' => auth()->user()->email,
                'timestamp' => now()
            ]);
            
            $this->userService->updateUser($userId, ['status' => $newStatus]);
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error in AdminUserController::delete: ' . $e->getMessage());
            return back()->with('error', 'Failed to update user status: ' . $e->getMessage());
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            abort(404);
        }

        // Generate a random password
        $newPassword = $this->generatePassword();

        // Log password reset action
        \Log::warning('User password reset by admin', [
            'affected_user_id' => $userId,
            'affected_user_email' => $user->email,
            'admin_user_id' => auth()->user()->userId,
            'admin_email' => auth()->user()->email,
            'timestamp' => now()
        ]);

        // Update user with new password
        $this->userService->updateUser($userId, ['password' => bcrypt($newPassword)]);

        // Send email with new password
        try {
            Mail::send('emails.password-reset', [
                'userName' => $user->name ?? '',
                'userEmail' => $user->email ?? '',
                'newPassword' => $newPassword,
                'userId' => $user->userId ?? $userId
            ], function ($message) use ($user) {
                $message->to($user->email ?? '')->subject('Your Password Has Been Reset');
            });
        } catch (\Exception $e) {
            // Log the error but still show success message
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
        }

        return redirect()->route('admin.user.passwordResetConfirm', [
            'userId' => $userId,
            'password' => $newPassword,
            'email' => $user->email ?? ''
        ]);
    }

    /**
     * Generate a random password
     */
    private function generatePassword($length = 10)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*';
        
        $all = $uppercase . $lowercase . $numbers . $special;
        $password = '';
        
        // Ensure at least one character from each category
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $special[rand(0, strlen($special) - 1)];
        
        // Fill the rest with random characters
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[rand(0, strlen($all) - 1)];
        }
        
        // Shuffle the password
        $password = str_shuffle($password);
        
        return $password;
    }

    /**
     * Show password reset confirmation page
     */
    public function passwordResetConfirm(Request $request, $userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            abort(404);
        }

        $newPassword = $request->query('password');
        $email = $request->query('email');

        return view('admin.userModule.passwordResetConfirm', [
            'user' => $user,
            'newPassword' => $newPassword,
            'email' => $email
        ]);
    }

    /**
     * Get users as JSON
     */
    public function getUsersJson()
    {
        $users = $this->userService->getAllUsersFromDb();
        $mappedUsers = array_map(function ($user) {
            return [
                'userId' => $user['userId'] ?? '',
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'role' => $user['role'] ?? '',
            ];
        }, is_array($users) ? $users : [$users]);
        
        return response()->json(['users' => $mappedUsers]);
    }

    /**
     * Batch ban/unban users
     */
    public function batchBan(Request $request)
    {
        $userIds = $request->input('userIds');
        $action = $request->input('action');

        // Convert comma-separated string to array
        $userIdArray = array_filter(array_map('trim', explode(',', $userIds)));

        $validated = $request->validate([
            'action' => 'required|in:ban,unban',
        ]);

        // Validate that all user IDs exist
        $allUsers = $this->userService->getAllUsersFromDb();
        $existingIds = array_map(function ($user) {
            return $user['userId'] ?? '';
        }, is_array($allUsers) ? $allUsers : [$allUsers]);
        
        $validIds = array_intersect($userIdArray, $existingIds);
        if (empty($validIds)) {
            return redirect()->route('admin.user.index')->with('error', 'No valid users selected');
        }

        $newStatus = $action === 'ban' ? 'banned' : 'active';
        $updated = 0;
        
        // Update each user via API
        foreach ($validIds as $userId) {
            $user = User::find($userId);
            $result = $this->userService->updateUser($userId, ['status' => $newStatus]);
            if ($result) {
                // Log batch action for each user
                \Log::warning('User status changed by admin (batch operation)', [
                    'action' => $action,
                    'affected_user_id' => $userId,
                    'affected_user_email' => $user->email ?? 'N/A',
                    'previous_status' => $user->status ?? 'N/A',
                    'new_status' => $newStatus,
                    'admin_user_id' => auth()->user()->userId,
                    'admin_email' => auth()->user()->email,
                    'timestamp' => now()
                ]);
                $updated++;
            }
        }

        // Log batch operation summary
        \Log::info('Batch user status change completed', [
            'action' => $action,
            'total_users_affected' => $updated,
            'admin_user_id' => auth()->user()->userId,
            'admin_email' => auth()->user()->email,
            'timestamp' => now()
        ]);

        $message = $action === 'ban' 
            ? "{$updated} user(s) banned successfully" 
            : "{$updated} user(s) unbanned successfully";

        return redirect()->route('admin.user.index')->with('success', $message);
    }

    /**
     * Display user statistics dashboard
     */
    public function statistics()
    {
        $allUsers = $this->userService->getAllUsersFromDb();
        $users = is_array($allUsers) ? $allUsers : [$allUsers];
        
        // Filter non-admin users
        $nonAdminUsers = array_filter($users, function ($user) {
            return ($user['role'] ?? null) !== 'admin';
        });
        
        // Total users count
        $totalUsers = count($nonAdminUsers);
        
        // Users by role
        $studentCount = count(array_filter($nonAdminUsers, function ($user) {
            return ($user['role'] ?? null) === 'student';
        }));
        $lecturerCount = count(array_filter($nonAdminUsers, function ($user) {
            return ($user['role'] ?? null) === 'lecturer';
        }));
        
        // Users by status
        $activeCount = count(array_filter($nonAdminUsers, function ($user) {
            return ($user['status'] ?? 'active') === 'active';
        }));
        $inactiveCount = count(array_filter($nonAdminUsers, function ($user) {
            return ($user['status'] ?? null) === 'inactive';
        }));
        $bannedCount = count(array_filter($nonAdminUsers, function ($user) {
            return ($user['status'] ?? null) === 'banned';
        }));
        
        // Users by class group
        $studentUsers = array_filter($nonAdminUsers, function ($user) {
            return ($user['role'] ?? null) === 'student';
        });
        $usersByClassGroup = [];
        $grouped = [];
        foreach ($studentUsers as $user) {
            $classGroupId = $user['classGroupId'] ?? 'Unassigned';
            if (!isset($grouped[$classGroupId])) {
                $grouped[$classGroupId] = [];
            }
            $grouped[$classGroupId][] = $user;
        }
        foreach ($grouped as $classGroupId => $group) {
            $usersByClassGroup[] = [
                'name' => $classGroupId === 'Unassigned' ? 'Unassigned' : $classGroupId,
                'count' => count($group)
            ];
        }
        
        // New users in last 30 days
        $newUsersThisMonth = count(array_filter($nonAdminUsers, function ($user) {
            if (!isset($user['created_at'])) return false;
            $createdDate = strtotime($user['created_at']);
            return $createdDate >= strtotime('-30 days');
        }));
        
        // Monthly user creation trend (last 12 months)
        $monthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();
            $count = count(array_filter($nonAdminUsers, function ($user) use ($startDate, $endDate) {
                if (!isset($user['created_at'])) return false;
                $createdDate = strtotime($user['created_at']);
                return $createdDate >= $startDate->timestamp && $createdDate <= $endDate->timestamp;
            }));
            $monthlyTrend[] = [
                'month' => $startDate->format('M Y'),
                'count' => $count
            ];
        }
        
        // Recently active users (updated within last 7 days)
        $recentlyActive = count(array_filter($nonAdminUsers, function ($user) {
            if (!isset($user['updated_at'])) return false;
            $updatedDate = strtotime($user['updated_at']);
            return $updatedDate >= strtotime('-7 days');
        }));

        return view('admin.userModule.user-statistics', [
            'totalUsers' => $totalUsers,
            'studentCount' => $studentCount,
            'lecturerCount' => $lecturerCount,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
            'bannedCount' => $bannedCount,
            'usersByClassGroup' => $usersByClassGroup,
            'newUsersThisMonth' => $newUsersThisMonth,
            'monthlyTrend' => $monthlyTrend,
            'recentlyActive' => $recentlyActive,
        ]);
    }
}
