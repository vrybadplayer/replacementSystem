<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentTimetableController;
use App\Http\Controllers\AdminTimetableController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\CourseSubjectController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReplacementController;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminUserController;


// Welcome/Landing Page
Route::get('/welcome', function () {
    return view('welcome');
});

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Admin Dashboard (Root for admin)
    Route::get('/dashboard', function () {
        return view('admin_dashboard');
    })->name('admin.dashboard');

    // User Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.user.index');
    Route::get('/users/filter', [AdminUserController::class, 'getFilteredUsers'])->name('admin.user.filter');
    Route::get('/users/statistics', [AdminUserController::class, 'statistics'])->name('admin.user.statistics');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('admin.user.create');
    Route::post('/users/store', [AdminUserController::class, 'store'])->name('admin.user.store');
    Route::post('/users/batch-ban', [AdminUserController::class, 'batchBan'])->name('admin.user.batchBan');
    Route::get('/users/{userId}', [AdminUserController::class, 'show'])->name('admin.user.show');
    Route::get('/users/{userId}/edit', [AdminUserController::class, 'edit'])->name('admin.user.edit');
    Route::post('/users/{userId}/update', [AdminUserController::class, 'update'])->name('admin.user.update');
    Route::delete('/users/{userId}', [AdminUserController::class, 'delete'])->name('admin.user.delete');
    Route::post('/users/{userId}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.user.resetPassword');
    Route::get('/users/{userId}/password-reset-confirm', [AdminUserController::class, 'passwordResetConfirm'])->name('admin.user.passwordResetConfirm');

    // Admin-specific routes
    Route::get('/timetableAssignment', [AdminTimetableController::class, 'viewTimetableAssignment'])->name('admin.timetableAssignment');
    Route::get('/courses', [CourseController::class, 'courseListView'])->name('admin.courses');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('admin.courses.create');
    Route::post('/courses/store', [CourseController::class, 'store'])->name('admin.courses.store');
    Route::get('/courses/{courseId}/edit', [CourseController::class, 'edit'])->name('admin.courses.edit');
    Route::post('/courses/{courseId}/update', [CourseController::class, 'update'])->name('admin.courses.update');
    Route::get('/subjects', [SubjectController::class, 'subjectListView'])->name('admin.subjects');
    Route::get('/subjects/create', [SubjectController::class, 'create'])->name('admin.subjects.create');
    Route::post('/subjects/store', [SubjectController::class, 'store'])->name('admin.subjects.store');
    Route::get('/subjects/{subjectId}/edit', [SubjectController::class, 'edit'])->name('admin.subjects.edit');
    Route::post('/subjects/{subjectId}/update', [SubjectController::class, 'update'])->name('admin.subjects.update');
    Route::get('/courseSubjects', [CourseSubjectController::class, 'courseSubjectListView'])->name('admin.courseSubjects');
    Route::get('/courseSubjects/create', [CourseSubjectController::class, 'create'])->name('admin.courseSubjects.create');
    Route::post('/courseSubjects/store', [CourseSubjectController::class, 'store'])->name('admin.courseSubjects.store');
    Route::get('/courseSubjects/{courseSubjectId}/edit', [CourseSubjectController::class, 'edit'])->name('admin.courseSubjects.edit');
    Route::post('/courseSubjects/{courseSubjectId}/update', [CourseSubjectController::class, 'update'])->name('admin.courseSubjects.update');

    // Admin Replacement Module
    Route::get('/replacement/appeals', [ReplacementController::class, 'adminViewAppeals'])->name('admin.replacement.appeals');
    Route::get('/replacement/appeals/{appealId}', [ReplacementController::class, 'adminViewAppealDetail'])->name('admin.replacement.detail');
    Route::post('/replacement/appeals/{appealId}/process', [ReplacementController::class, 'adminProcessAppeal'])->name('admin.replacement.process');

    // Admin Timetable Assignment Module
    Route::post('/assignment/submit', [AdminTimetableController::class, 'storeTimetable'])->name('admin.assignment.submit');

    // Admin Timeslot Management Module
    Route::get('/timeslotManagement', [AdminTimetableController::class, 'viewTimeslotManagement'])->name('admin.timeslotManagement');
    Route::post('/timeslotManagement', [AdminTimetableController::class, 'filterTimeslots'])->name('admin.timeslot.filter');
    Route::post('/timeslotManagement/update', [AdminTimetableController::class, 'updateTimeslot'])->name('admin.timeslot.update');
    Route::delete('/timeslotManagement/delete', [AdminTimetableController::class, 'deleteTimeslot'])->name('admin.timeslot.delete');
});


// Student Routes
Route::middleware(['auth', 'role:student'])->prefix('student')->group(function () {
    // Student Dashboard
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $enrolledCourses = CourseEnrollment::where('userID', $user->userId)->with('course')->get();
        $availableCourses = Course::all();
        
        return view('student_dashboard', [
            'enrolledCourses' => $enrolledCourses,
            'availableCourses' => $availableCourses,
            'hasEnrollments' => count($enrolledCourses) > 0
        ]);
    })->name('student.dashboard');

    // Enroll in course
    Route::post('/enroll-course', function () {
        $user = Auth::user();
        $courseId = request('courseId');
        
        // Check if already enrolled
        $exists = CourseEnrollment::where('userID', $user->userId)
            ->where('courseID', $courseId)
            ->exists();
        
        if ($exists) {
            return redirect()->route('student.dashboard')->with('error', 'You are already enrolled in this course');
        }
        
        // Generate enrollment ID
        $lastEnrollment = CourseEnrollment::orderBy('enrollmentID', 'desc')->first();
        $nextNumber = 1;
        if ($lastEnrollment) {
            preg_match('/CE(\d+)/', $lastEnrollment->enrollmentID, $matches);
            $nextNumber = intval($matches[1]) + 1;
        }
        $enrollmentId = 'CE' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        // Create enrollment
        CourseEnrollment::create([
            'enrollmentID' => $enrollmentId,
            'userID' => $user->userId,
            'courseID' => $courseId,
            'year' => date('Y'),
            'semester' => request('semester', 1),
        ]);
        
        return redirect()->route('student.dashboard')->with('success', 'Successfully enrolled in the course!');
    })->name('student.enrollCourse');

    // Student Profile module
    Route::get('/profile', [UserController::class, 'showProfile'])->name('student.profile');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('student.changePassword');

    // Timetable module
    Route::get('/timetable', [StudentTimetableController::class, 'index'])->name('student.timetable');
    Route::get('/selectTimeslot', [StudentTimetableController::class, 'viewSelectTimeslot'])->name('student.selectTimeslot');
    Route::post('/selectTimeslot/store', [StudentTimetableController::class, 'storeSelectedClass'])->name('student.selectTimeslot.store');
    
    // Replacement Appeal module
    Route::get('/replacement/appeal', [ReplacementController::class, 'showAppealForm'])->name('replacement.appeal');
    Route::post('/replacement/submit', [ReplacementController::class, 'submitAppeal'])->name('replacement.submit');
    Route::get('/replacement/my-appeals', [ReplacementController::class, 'myAppeals'])->name('replacement.myAppeals');
});



// Root route - redirect to login
Route::get('/', function () {
    return redirect('/login');
})->name('home');
