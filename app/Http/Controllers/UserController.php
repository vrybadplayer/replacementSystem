<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CourseEnrollment;
use App\Facades\CourseSubjectManagementFacade;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = $this->userService->getUsersForView();
        return view('user.listUsers', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('user.addUser');
    }

    /**
     * Store a newly created user in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'userId' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email',
            'role' => 'required|string',
        ]);

        $this->userService->createUser($validated);
        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    /**
     * Display the specified user
     */
    public function show($userId)
    {
        $user = $this->userService->getUserProfileForView($userId);
        
        if (!$user) {
            abort(404);
        }

        return view('user.showUser', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($userId)
    {
        $user = $this->userService->getUserById($userId);
        
        if (!$user) {
            abort(404);
        }

        return view('user.editUser', compact('user'));
    }

    /**
     * Update the specified user in database
     */
    public function update(Request $request, $userId)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'role' => 'sometimes|string',
        ]);

        $this->userService->updateUser($userId, $validated);
        return redirect()->route('users.show', $userId)->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user from database
     */
    public function destroy($userId)
    {
        $this->userService->deleteUser($userId);
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }

    // ---------- AUTHENTICATED USER METHODS ----------

    /**
     * Get user profile for authenticated user
     */
    public function showProfile()
    {
        $user = Auth::user();
        $enrolledCourses = $this->userService->getEnrolledCourses($user->userId);
        
        \Log::info('Profile page loaded for user', [
            'user_id' => $user->userId,
            'enrolled_courses_count' => count($enrolledCourses ?? []),
            'enrolled_courses' => $enrolledCourses
        ]);
        
        // Get all course subjects with error handling - using DATABASE directly
        $subjects = [];
        if (!empty($enrolledCourses)) {
            try {
                $courseIds = array_column($enrolledCourses, 'courseID');
                
                \Log::info('Fetching course subjects from database', [
                    'user_id' => $user->userId,
                    'course_ids' => $courseIds
                ]);
                
                // Get all course subjects from DATABASE with relationships
                $courseSubjects = \App\Models\CourseSubject::with(['course', 'subject'])
                    ->whereIn('courseId', $courseIds)
                    ->get();
                
                \Log::info('Database returned course subjects', [
                    'count' => count($courseSubjects),
                    'sample' => $courseSubjects->count() > 0 ? $courseSubjects[0]->toArray() : null
                ]);
                
                // Build subjects list
                foreach ($courseSubjects as $courseSubject) {
                    if ($courseSubject->subject) {
                        $subject = $courseSubject->subject;
                        $course = $courseSubject->course;
                        
                        \Log::info('Processing subject', [
                            'subjectId' => $subject->subjectId,
                            'subjectName' => $subject->subjectName,
                            'courseId' => $course->courseId ?? null
                        ]);
                        
                        // Find the corresponding enrolled course to get enrollment year/semester
                        $enrollmentInfo = array_values(array_filter($enrolledCourses, function($c) use ($course) {
                            return $c['courseID'] === $course->courseId;
                        }));
                        
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
                
                \Log::info('Final subjects list from database', [
                    'count' => count($subjects),
                    'subjects' => $subjects
                ]);
            } catch (\Exception $e) {
                \Log::error('Error fetching subjects from database', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => $user->userId
                ]);
            }
        } else {
            \Log::info('No enrolled courses found', ['user_id' => $user->userId]);
        }
        
        \Log::info('Profile page data prepared', [
            'user_id' => $user->userId,
            'subjects_count' => count($subjects),
            'courses_count' => count($enrolledCourses ?? [])
        ]);
        
        return view('student.userModule.profile', ['user' => $user, 'enrolledCourses' => $enrolledCourses, 'subjects' => $subjects]);
    }

    /**
     * Get profile data with enrollments
     */
    public function getProfileData()
    {
        $user = Auth::user();
        
        $profileData = $this->userService->getProfileData($user->userId);

        return response()->json($profileData ?? []);
    }

    /**
     * Get enrolled courses for authenticated user
     */
    public function getEnrolledCourses()
    {
        $user = Auth::user();
        
        $courses = $this->userService->getEnrolledCourses($user->userId);

        return response()->json(['courses' => $courses ?? []]);
    }

    /**
     * Update profile for authenticated user
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $name = $request->input('name');
        $email = $request->input('email');

        if (empty($name) || empty($email)) {
            return response()->json(['error' => 'Name and email required'], 400);
        }

        // Log profile update attempt
        \Log::info('User profile update attempted', [
            'user_id' => $user->userId,
            'email' => $user->email,
            'updated_fields' => ['name', 'email'],
            'ip_address' => $request->ip(),
            'timestamp' => now()
        ]);

        $updated = $this->userService->updateUser($user->userId, [
            'name' => $name,
            'email' => $email
        ]);

        return response()->json(['success' => true, 'message' => 'Updated', 'user' => $updated]);
    }

    // ---------- API ENDPOINTS ----------

    /**
     * API: Get all users
     */
    public function apiIndex()
    {
        $users = $this->userService->getAllUsers();
        return response()->json($users);
    }

    /**
     * API: Get single user by ID
     */
    public function apiShow($id)
    {
        $user = User::find($id);

        if (!$user || empty($user)) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    /**
     * API: Get user by ID from database
     */
    public function apiShowDb($id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    /**
     * API: Create a new user
     */
    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'userId' => 'required|string|unique:users',
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'role' => 'required|string',
        ]);

        $user = $this->userService->createUser($validated);
        return response()->json($user, 201);
    }

    /**
     * API: Update a user
     */
    public function apiUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'role' => 'sometimes|string',
        ]);

        $user = $this->userService->updateUser($id, $validated);
        return response()->json($user);
    }

    /**
     * API: Delete a user
     */
    public function apiDestroy($id)
    {
        $deleted = $this->userService->deleteUser($id);

        if ($deleted) {
            return response()->json(['message' => 'User deleted'], 200);
        }

        return response()->json(['error' => 'Failed to delete user'], 400);
    }

    /**
     * API: Get user profile with enrollments
     */
    public function apiGetProfileData($userId)
    {
        $profileData = $this->userService->getProfileData($userId);
        
        if (!$profileData) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($profileData);
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'confirmed'
            ],
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase, and numbers',
            'password.min' => 'Password must be at least 8 characters',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!\Hash::check($validated['current_password'], $user->password)) {
            // Log failed password change attempt
            \Log::warning('Password change failed - incorrect current password', [
                'user_id' => $user->userId,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'timestamp' => now()
            ]);
            
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        // Update password
        $user->password = \Hash::make($validated['password']);
        $user->save();

        // Log successful password change
        \Log::info('User password changed successfully', [
            'user_id' => $user->userId,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'timestamp' => now()
        ]);

        return redirect()->route('student.profile')->with('success', 'Password changed successfully!');
    }
}

