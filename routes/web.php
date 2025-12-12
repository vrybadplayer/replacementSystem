<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\LoginController;

// Welcome/Landing Page
Route::get('/welcome', function () {
    return view('welcome');
});

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Admin Dashboard (Root for admin)
    Route::get('/dashboard', function () {
        return view('admin_dashboard');
    })->name('admin.dashboard');
    
    // Admin-specific routes
    Route::get('/subjectAssignment', [TimetableController::class, 'viewSubjectAssignment'])->name('admin.subjectAssignment');
    Route::get('/timeslotManagement', [TimetableController::class, 'viewTimeslotManagement'])->name('admin.timeslotManagement');
    Route::get('/courses', [CourseController::class, 'index'])->name('admin.courses');
    Route::get('/subjects', [SubjectController::class, 'index'])->name('admin.subjects');
});

// Student Routes
Route::middleware(['auth', 'role:student'])->prefix('student')->group(function () {
    // Student Dashboard
    Route::get('/dashboard', function () {
        return view('student_dashboard');
    })->name('student.dashboard');
    
    // Student-specific routes
    Route::get('/timetable', [TimetableController::class, 'index'])->name('student.timetable');
    Route::get('/replacementOptions', [TimetableController::class, 'viewOptions'])->name('student.replacementOptions');
    Route::get('/selectTimeslot', [TimetableController::class, 'selectTimeslot'])->name('student.selectTimeslot');
});

// Shared authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/replacement/appeal', function () {
        return "This is the Replacement Appeal Page (Out of Scope for this task)";
    })->name('replacement.appeal');
});

// Redirect root to login or appropriate dashboard
Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('student.dashboard');
    }
    return redirect()->route('login');
});