<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentTimetableController;
use App\Http\Controllers\AdminTimetableController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReplacementController;


// Welcome/Landing Page
Route::get('/welcome', function () {
    return view('welcome');
});

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
});
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Admin Dashboard (Root for admin)
    Route::get('/dashboard', function () {
        return view('admin_dashboard');
    })->name('admin.dashboard');
    
    // Admin-specific routes
    Route::get('/subjectAssignment', [AdminTimetableController::class, 'viewSubjectAssignment'])->name('admin.subjectAssignment');
    Route::get('/timeslotManagement', [AdminTimetableController::class, 'viewTimeslotManagement'])->name('admin.timeslotManagement');
    Route::get('/courses', [CourseController::class, 'index'])->name('admin.courses');
    Route::get('/subjects', [SubjectController::class, 'subjectListView'])->name('admin.subjects');
});

// Student Routes
Route::middleware(['auth', 'role:student'])->prefix('student')->group(function () {
    // Student Dashboard
    Route::get('/dashboard', function () {
        return view('student_dashboard');
    })->name('student.dashboard');
    
    // Timetable module
    Route::get('/timetable', [StudentTimetableController::class, 'index'])->name('student.timetable');
    Route::get('/replacementOptions', [StudentTimetableController::class, 'viewOptions'])->name('student.replacementOptions');
    Route::get('/selectTimeslot', [StudentTimetableController::class, 'selectTimeslot'])->name('student.selectTimeslot');
    
    // Replacement Appeal module
    Route::get('/replacement/appeal', [ReplacementController::class, 'showAppealForm'])->name('replacement.appeal');
    Route::post('/replacement/submit', [ReplacementController::class, 'submitAppeal'])->name('replacement.submit');
    Route::get('/replacement/my-appeals', [ReplacementController::class, 'myAppeals'])->name('replacement.myAppeals');
});

// Admin Replacement Module
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/replacement/appeals', [ReplacementController::class, 'adminViewAppeals'])->name('admin.replacement.appeals');
    Route::get('/replacement/appeals/{appealId}', [ReplacementController::class, 'adminViewAppealDetail'])->name('admin.replacement.detail');
    Route::post('/replacement/appeals/{appealId}/process', [ReplacementController::class, 'adminProcessAppeal'])->name('admin.replacement.process');
});

// Root route - redirect to login
Route::get('/', function () {
    return redirect('/login');
})->name('home');

// Replacement Module (Admin)
Route::get('/admin/replacement/appeals', [ReplacementController::class, 'adminViewAppeals'])->name('admin.replacement.appeals');
Route::get('/admin/replacement/appeal/{id}', [ReplacementController::class, 'adminViewAppealDetail'])->name('admin.replacement.detail');
Route::post('/admin/replacement/appeal/{id}/process', [ReplacementController::class, 'adminProcessAppeal'])->name('admin.replacement.process');
