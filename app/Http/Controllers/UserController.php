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
            'userId' => 'required|string|unique:users',
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
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
        
        // Get all course subjects with error handling
        $subjects = [];
        if (!empty($enrolledCourses)) {
            try {
                $courseIds = array_column($enrolledCourses, 'courseID');
                
                // Get all course subjects from API
                $allCourseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi();
                
                // Filter by enrolled courses
                $enrolledCourseSubjects = array_filter($allCourseSubjects, function($cs) use ($courseIds) {
                    return in_array($cs['courseId'] ?? null, $courseIds);
                });
                
                // Build subjects list
                foreach ($enrolledCourseSubjects as $courseSubject) {
                    $subjectId = $courseSubject['subjectId'] ?? null;
                    if ($subjectId) {
                        // Get subject details from API
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
        
        return view('student.userModule.profile', ['user' => $user, 'enrolledCourses' => $enrolledCourses, 'subjects' => $subjects]);
    }

    /**
     * Get profile data with enrollments
     */
    public function getProfileData()
    {
        $user = Auth::user();
        
        $enrollments = CourseEnrollment::where('userID', $user->userId)->with('course')->get();

        return response()->json([
            'user' => [
                'userId' => $user->userId,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'enrollments' => $enrollments,
            'count' => count($enrollments),
        ]);
    }

    /**
     * Get enrolled courses for authenticated user
     */
    public function getEnrolledCourses()
    {
        $user = Auth::user();
        
        $courses = CourseEnrollment::where('userID', $user->userId)
            ->with('course')
            ->get();

        $result = [];
        foreach ($courses as $enrollment) {
            $result[] = [
                'enrollmentID' => $enrollment->enrollmentID,
                'courseID' => $enrollment->courseID,
                'courseName' => $enrollment->course->courseName,
                'courseCode' => $enrollment->course->courseCode,
                'year' => $enrollment->year,
                'semester' => $enrollment->semester,
            ];
        }

        return response()->json(['courses' => $result]);
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

        $user->name = $name;
        $user->email = $email;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Updated']);
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
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        // Update password
        $user->password = \Hash::make($validated['password']);
        $user->save();

        return redirect()->route('student.profile')->with('success', 'Password changed successfully!');
    }
}

