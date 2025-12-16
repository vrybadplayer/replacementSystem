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
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\VenueController;


// This file is what ApiService files will call

// --- Authentication API ---
Route::post('/login', [LoginController::class, 'apiLogin']);
Route::post('/logout', [LoginController::class, 'apiLogout'])->middleware('auth');
Route::get('/auth/user', [LoginController::class, 'apiGetUser'])->middleware('auth');

// --- User Management API (Admin) ---
Route::get('/admin/users', [AdminUserController::class, 'getUsersJson']);
Route::get('/users', [UserController::class, 'apiIndex']);
Route::post('/users', [UserController::class, 'apiStore']);
Route::get('/users/{id}', [UserController::class, 'apiShow']);
Route::get('/users/{id}/db', [UserController::class, 'apiShowDb']);
Route::put('/users/{id}', [UserController::class, 'apiUpdate']);
Route::delete('/users/{id}', [UserController::class, 'apiDestroy']);
Route::get('/users/{userId}/profile', [UserController::class, 'apiGetProfileData']);
Route::get('/users/{userId}/courses', [UserController::class, 'apiGetEnrolledCourses']);

// --- Student Profile API ---
Route::get('/user/profile', [UserController::class, 'getProfileData']);
Route::get('/user/courses', [UserController::class, 'getEnrolledCourses']);
Route::post('/user/profile/update', [UserController::class, 'updateProfile']);

// Timetable Module API
Route::get('/', [StudentTimetableController::class, '']);
Route::get('/venues', [VenueController::class, 'getAllVenue']);
Route::get('/venues/{id}', [VenueController::class, 'getVenueById']);
Route::get('/classgroups', [ClassGroupController::class, 'getAllClassGroups']);
Route::get('/classgroups/{id}', [ClassGroupController::class, 'getClassGroupById']);
Route::get('/timetable', [TimetableController::class, 'getAllTimetableRecords']);
Route::get('/timetable/{id}', [TimetableController::class, 'getTimetableById']);

// --- Course Management API ---
Route::get('/courses', [CourseController::class, 'index']);

// --- Course Subject Bridge REST API ---
Route::get('/courseSubject', [CourseSubjectController::class, 'index']);
Route::post('/courseSubject', [CourseSubjectController::class, 'store']);
Route::put('/courseSubject/{id}', [CourseSubjectController::class, 'update']);
Route::patch('/courseSubject/{id}', [CourseSubjectController::class, 'update']);

// --- Subjects REST API ---
Route::get('/subjects', [SubjectController::class, 'index']);
Route::post('/subjects', [SubjectController::class, 'store']);
Route::put('/subjects/{id}', [SubjectController::class, 'update']);
Route::patch('/subjects/{id}', [SubjectController::class, 'update']);


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
