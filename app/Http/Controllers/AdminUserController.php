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

        $enrolledCourses = $this->userService->getEnrolledCourses($userId);
        
        // Get all course subjects with error handling
        $subjects = [];
        if (!empty($enrolledCourses)) {
            try {
                $courseIds = array_column($enrolledCourses, 'courseID');
                
                // Get all course subjects from API (one call for all)
                $allCourseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi();
                
                // Filter by enrolled courses
                $enrolledCourseSubjects = array_filter($allCourseSubjects, function($cs) use ($courseIds) {
                    return in_array($cs['courseId'] ?? null, $courseIds);
                });
                
                // Build subjects list
                foreach ($enrolledCourseSubjects as $courseSubject) {
                    $subjectId = $courseSubject['subjectId'] ?? null;
                    if ($subjectId) {
                        // Get subject details from API using Facade
                        $subjectDetails = CourseSubjectManagementFacade::getSubjectByIdFromApi($subjectId);
                        
                        // Find course info
                        $courseInfo = array_values(array_filter($enrolledCourses, function($c) use ($courseSubject) {
                            return $c['courseID'] === ($courseSubject['courseId'] ?? null);
                        }));
                        
                        if (!empty($courseInfo)) {
                            $course = $courseInfo[0];
                            $subjects[] = [
                                'subjectId' => $subjectDetails['subjectId'] ?? $subjectId,
                                'subjectCode' => $subjectDetails['subjectCode'] ?? '',
                                'subjectName' => $subjectDetails['subjectName'] ?? '',
                                'creditHours' => $subjectDetails['creditHours'] ?? 0,
                                'category' => $subjectDetails['category'] ?? '',
                                'courseName' => $course['courseName'],
                                'courseCode' => $course['courseCode'],
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Fall back to database if API is unavailable
                foreach ($enrolledCourses as $course) {
                    $courseId = $course['courseID'];
                    
                    $courseSubjects = \App\Models\CourseSubject::where('courseId', $courseId)
                        ->with('subject')
                        ->get();
                    
                    foreach ($courseSubjects as $courseSubject) {
                        $subject = $courseSubject->subject;
                        if ($subject) {
                            $subjects[] = [
                                'subjectId' => $subject->subjectId,
                                'subjectCode' => $subject->subjectCode,
                                'subjectName' => $subject->subjectName,
                                'creditHours' => $subject->creditHours,
                                'category' => $subject->category,
                                'courseName' => $course['courseName'],
                                'courseCode' => $course['courseCode'],
                            ];
                        }
                    }
                }
            }
        }
        
        return view('admin.userModule.showUser', ['user' => $user, 'enrolledCourses' => $enrolledCourses, 'subjects' => $subjects]);
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
     * Reset user password
     */
    public function resetPassword($userId)
    {
        $user = $this->userService->getUserById($userId);

        if (!$user) {
            abort(404);
        }

        // Generate a random password
        $newPassword = $this->generatePassword();
        $user->password = bcrypt($newPassword);
        $user->save();

        // Send email with new password
        try {
            Mail::send('emails.password-reset', [
                'userName' => $user->name,
                'userEmail' => $user->email,
                'newPassword' => $newPassword,
                'userId' => $user->userId
            ], function ($message) use ($user) {
                $message->to($user->email)->subject('Your Password Has Been Reset');
            });
        } catch (\Exception $e) {
            // Log the error but still show success message
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
        }

        return redirect()->route('admin.user.passwordResetConfirm', [
            'userId' => $userId,
            'password' => $newPassword,
            'email' => $user->email
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
        $user = $this->userService->getUserById($userId);
        
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
        $existingUsers = User::whereIn('userId', $userIdArray)->count();
        if ($existingUsers === 0) {
            return redirect()->route('admin.user.index')->with('error', 'No valid users selected');
        }

        $newStatus = $action === 'ban' ? 'banned' : 'active';
        $updated = User::whereIn('userId', $userIdArray)->update(['status' => $newStatus]);

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
        // Total users count
        $totalUsers = User::where('role', '!=', 'admin')->count();
        
        // Users by role
        $studentCount = User::where('role', 'student')->count();
        $lecturerCount = User::where('role', 'lecturer')->count();
        
        // Users by status
        $activeCount = User::where('role', '!=', 'admin')->where('status', 'active')->count();
        $inactiveCount = User::where('role', '!=', 'admin')->where('status', 'inactive')->count();
        $bannedCount = User::where('role', '!=', 'admin')->where('status', 'banned')->count();
        
        // Users by class group
        $usersByClassGroup = User::where('role', 'student')
            ->with('classGroup')
            ->get()
            ->groupBy('classGroupId')
            ->map(function ($group) {
                $classGroupName = $group->first()->classGroup?->classGroupName ?? 'Unassigned';
                return [
                    'name' => $classGroupName,
                    'count' => $group->count()
                ];
            })
            ->values();
        
        // New users in last 30 days
        $newUsersThisMonth = User::where('role', '!=', 'admin')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        
        // Monthly user creation trend (last 12 months)
        $monthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();
            $count = User::where('role', '!=', 'admin')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            $monthlyTrend[] = [
                'month' => $startDate->format('M Y'),
                'count' => $count
            ];
        }
        
        // Active users (logged in within last 7 days) - if you have a last_login field
        // For now, we'll use created_at as a proxy
        $recentlyActive = User::where('role', '!=', 'admin')
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

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
