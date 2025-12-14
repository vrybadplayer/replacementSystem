<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\StudentTimetableController;
use App\Http\Controllers\AdminTimetableController;
use App\Http\Controllers\CourseSubjectController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminUserController;


// This file is what ApiService files will call

// --- Authentication API ---
Route::post('/login', [LoginController::class, 'apiLogin']);
Route::post('/logout', [LoginController::class, 'apiLogout'])->middleware('auth');
Route::get('/auth/user', [LoginController::class, 'apiGetUser'])->middleware('auth');

// --- User Management API (Admin) ---
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users', [AdminUserController::class, 'getUsersJson']);
});
Route::get('/users/{id}', [UserController::class, 'apiShow']);

// --- Student Profile API ---
Route::middleware('auth')->group(function () {
    Route::get('/user/profile', [UserController::class, 'getProfileData']);
    Route::get('/user/courses', [UserController::class, 'getEnrolledCourses']);
    Route::post('/user/profile/update', [UserController::class, 'updateProfile']);
});

// Timetable Module API
Route::get('/', [StudentTimetableController::class, '']);
Route::get('/testing', [StudentTimetableController::class, 'test']);


// --- Course Management API ---
Route::get('/courses', [CourseController::class, 'apiIndex']);
Route::get('/courses2', [CourseController::class, 'apiIndex2']);

// --- Course Subject Bridge REST API ---
Route::get('/courseSubject', [CourseSubjectController::class, 'getAllCourseSubjects']);

// --- Subjects REST API ---
Route::get('/subjects', [SubjectController::class, 'index']); 
Route::get('/subjects/{id}', [SubjectController::class, 'show']); 
Route::post('/subjects', [SubjectController::class, 'store']); 
Route::put('/subjects/{id}', [SubjectController::class, 'update']);
Route::patch('/subjects/{id}', [SubjectController::class, 'update']); 
Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);


// --- User Management API ---
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth')->group(function () {
    Route::get('/user/profile', [UserController::class, 'getProfileData']);
    Route::get('/user/courses', [UserController::class, 'getEnrolledCourses']);
    Route::post('/user/profile/update', [UserController::class, 'updateProfile']);
});

// --- Replacement Module API ---