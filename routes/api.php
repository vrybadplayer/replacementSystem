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
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\ReplacementController;
use App\Http\Controllers\TimetableController;


// This file is what ApiService files will call

// --- Authentication API ---
Route::post('/login', [LoginController::class, 'apiLogin']);
Route::post('/logout', [LoginController::class, 'apiLogout'])->middleware('auth');
Route::get('/auth/user', [LoginController::class, 'apiGetUser'])->middleware('auth');

// --- User Management API (Admin) ---
Route::middleware('auth')->get('/admin/users', [AdminUserController::class, 'getUsersJson']);
Route::middleware('auth')->get('/users', [UserController::class, 'apiIndex']);
Route::middleware('auth')->post('/users', [UserController::class, 'apiStore']);
Route::middleware('auth')->get('/users/{id}', [UserController::class, 'apiShow']);
Route::middleware('auth')->get('/users/{id}/db', [UserController::class, 'apiShowDb']);
Route::middleware('auth')->put('/users/{id}', [UserController::class, 'apiUpdate']);
Route::middleware('auth')->delete('/users/{id}', [UserController::class, 'apiDestroy']);
Route::middleware('auth')->get('/users/{userId}/profile', [UserController::class, 'apiGetProfileData']);
Route::middleware('auth')->get('/users/{userId}/courses', [UserController::class, 'apiGetEnrolledCourses']);

// --- Student Profile API ---
Route::middleware('auth')->get('/user/profile', [UserController::class, 'getProfileData']);
Route::middleware('auth')->get('/user/courses', [UserController::class, 'getEnrolledCourses']);
Route::middleware('auth')->post('/user/profile/update', [UserController::class, 'updateProfile']);

// Timetable Module API
Route::middleware('auth')->get('/', [StudentTimetableController::class, '']);
Route::middleware('auth')->get('/venues', [VenueController::class, 'getAllVenues']);
Route::middleware('auth')->get('/venues/{id}', [VenueController::class, 'getVenueById']);
Route::middleware('auth')->get('/classgroups', [ClassGroupController::class, 'getAllClassGroups']);
Route::middleware('auth')->get('/classgroups/{id}', [ClassGroupController::class, 'getClassGroupById']);
Route::middleware('auth')->get('/timetable', [TimetableController::class, 'getAllTimetableRecords']);
Route::middleware('auth')->get('/timetable/{id}', [TimetableController::class, 'getTimetableById']);
Route::middleware('auth')->get('/occupiedSlots', [TimetableController::class, 'getOccupiedSlots']);
Route::middleware('auth')->get('/clashCheck', [TimetableController::class, 'clashCheck']);
Route::middleware('auth')->post('/timetable/create', [TimetableController::class, 'createTimetable']);
Route::middleware('auth')->get('/timetable/{classGroupId}', [TimetableController::class, 'getTimetableByClassGroupId']);
Route::middleware('auth')->get('/timetable/future/{userId}', [TimetableController::class, 'getFutureClasses']);

// --- Course Management API ---
Route::middleware('auth')->get('/courses', [CourseController::class, 'index']);
Route::middleware('auth')->get('/courses/{courseId}', [CourseController::class, 'apiShow']);
Route::middleware('auth')->post('/courses', [CourseController::class, 'apiStore']);
Route::middleware('auth')->put('/courses/{courseId}', [CourseController::class, 'apiUpdate']);
Route::middleware('auth')->patch('/courses/{courseId}', [CourseController::class, 'apiUpdate']);

// --- Course Subject Bridge REST API ---
Route::middleware('auth')->get('/courseSubject', [CourseSubjectController::class, 'index']);
Route::middleware('auth')->get('/courseSubject/{courseSubjectId}', [CourseSubjectController::class, 'apiShow']);
Route::middleware('auth')->post('/courseSubject', [CourseSubjectController::class, 'apiStore']);
Route::middleware('auth')->put('/courseSubject/{id}', [CourseSubjectController::class, 'apiUpdate']);
Route::middleware('auth')->patch('/courseSubject/{id}', [CourseSubjectController::class, 'apiUpdate']);
Route::middleware('auth')->get('/courseSubject/fetchCourseSubjectWithForeignKeys', [CourseSubjectController::class, 'fetchCourseSubjectWithForeignKeys']);
Route::middleware('auth')->get('/courseSubjects/byCourse/{courseId}', [CourseSubjectController::class, 'getCourseSubjectsByCourse']);

// --- Subjects REST API ---
Route::middleware('auth')->get('/subjects', [SubjectController::class, 'index']);
Route::middleware('auth')->get('/subjects/{subjectId}', [SubjectController::class, 'apiShow']);
Route::middleware('auth')->post('/subjects', [SubjectController::class, 'apiStore']);
Route::middleware('auth')->put('/subjects/{id}', [SubjectController::class, 'apiUpdate']);
Route::middleware('auth')->patch('/subjects/{id}', [SubjectController::class, 'apiUpdate']);
Route::middleware('auth')->get('/subject/byCourseSubject/{courseSubjectId}', [SubjectController::class, 'getSubjectByCourseSubject']);
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
Route::middleware('auth')->prefix('replacement')->group(function () {
    // Appeals
    Route::get('/appeals', [ReplacementController::class, 'getAllAppeals']);
    Route::get('/appeals/pending', [ReplacementController::class, 'getPendingAppeals']);
    Route::get('/appeals/user/{userId}', [ReplacementController::class, 'getUserAppeals']);
    Route::get('/appeals/{appealId}', [ReplacementController::class, 'getAppeal']);
    Route::post('/appeals', [ReplacementController::class, 'createAppeal']);
    Route::post('/appeals/{appealId}/process', [ReplacementController::class, 'processAppeal']);
    
    // Replacements
    Route::get('/replacements/user/{userId}', [ReplacementController::class, 'getUserReplacements']);
    Route::get('/validOptions', [ReplacementController::class, 'validReplacementOptions']);
    
    // Statistics
    Route::get('/statistics', [ReplacementController::class, 'getStatistics']);
});
