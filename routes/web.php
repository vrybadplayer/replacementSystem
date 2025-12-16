<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentTimetableController;
use App\Http\Controllers\AdminTimetableController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\CourseSubjectController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReplacementController;
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
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('admin.user.create');
    Route::post('/users/store', [AdminUserController::class, 'store'])->name('admin.user.store');
    Route::get('/users/{userId}', [AdminUserController::class, 'show'])->name('admin.user.show');
    Route::get('/users/{userId}/edit', [AdminUserController::class, 'edit'])->name('admin.user.edit');
    Route::post('/users/{userId}/update', [AdminUserController::class, 'update'])->name('admin.user.update');
    Route::delete('/users/{userId}', [AdminUserController::class, 'delete'])->name('admin.user.delete');
    Route::post('/users/{userId}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.user.resetPassword');
    Route::get('/users/{userId}/password-reset-confirm', [AdminUserController::class, 'passwordResetConfirm'])->name('admin.user.passwordResetConfirm');
    
    // Admin-specific routes
    Route::get('/timetableAssignment', [AdminTimetableController::class, 'viewTimetableAssignment'])->name('admin.timetableAssignment');
    Route::get('/timeslotManagement', [AdminTimetableController::class, 'viewTimeslotManagement'])->name('admin.timeslotManagement');
    Route::get('/courses', [CourseController::class, 'courseListView'])->name('admin.courses');
    Route::get('/subjects', [SubjectController::class, 'subjectListView'])->name('admin.subjects');
    Route::get('/courseSubjects', [CourseSubjectController::class, 'courseSubjectListView'])->name('admin.courseSubjects');

    // Admin Replacement Module
    Route::get('/replacement/appeals', [ReplacementController::class, 'adminViewAppeals'])->name('admin.replacement.appeals');
    Route::get('/replacement/appeals/{appealId}', [ReplacementController::class, 'adminViewAppealDetail'])->name('admin.replacement.detail');
    Route::post('/replacement/appeals/{appealId}/process', [ReplacementController::class, 'adminProcessAppeal'])->name('admin.replacement.process');

    // Admin Timetable Assignment Module
    Route::get('/ajax/filter-subjects', [AdminTimetableController::class, 'ajaxFilterSubjects']);
    Route::post('/assignment/submit', [AdminTimetableController::class, 'storeTimetable'])->name('admin.assignment.submit');
});


// Student Routes
Route::middleware(['auth', 'role:student'])->prefix('student')->group(function () {
    // Student Dashboard
    Route::get('/dashboard', function () {
        return view('student_dashboard');
    })->name('student.dashboard');
    
    // Student Profile module
    Route::get('/profile', [UserController::class, 'showProfile'])->name('student.profile');
    
    // Timetable module
    Route::get('/timetable', [StudentTimetableController::class, 'index'])->name('student.timetable');
    Route::get('/replacementOptions', [StudentTimetableController::class, 'viewOptions'])->name('student.replacementOptions');
    Route::get('/selectTimeslot', [StudentTimetableController::class, 'selectTimeslot'])->name('student.selectTimeslot');
    
    // Replacement Appeal module
    Route::get('/replacement/appeal', [ReplacementController::class, 'showAppealForm'])->name('replacement.appeal');
    Route::post('/replacement/submit', [ReplacementController::class, 'submitAppeal'])->name('replacement.submit');
    Route::get('/replacement/my-appeals', [ReplacementController::class, 'myAppeals'])->name('replacement.myAppeals');
});



// Root route - redirect to login
Route::get('/', function () {
    return redirect('/login');
})->name('home');
