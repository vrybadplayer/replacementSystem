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
Route::get('/venues', [VenueController::class, 'getAllVenues']);
Route::get('/venues/{id}', [VenueController::class, 'getVenueById']);
Route::get('/classgroups', [ClassGroupController::class, 'getAllClassGroups']);
Route::get('/classgroups/{id}', [ClassGroupController::class, 'getClassGroupById']);
Route::get('/timetable', [TimetableController::class, 'getAllTimetableRecords']);
Route::get('/timetable/search', [TimetableController::class, 'searchTimetable']);
Route::get('/timetable/{id}', [TimetableController::class, 'getTimetableById']);
Route::get('/occupiedSlots', [TimetableController::class, 'getOccupiedSlots']);
Route::get('/clashCheck', [TimetableController::class, 'clashCheck']);
Route::post('/timetable/create', [TimetableController::class, 'createTimetable']);
Route::get('/timetable/{classGroupId}', [TimetableController::class, 'getTimetableByClassGroupId']);
Route::get('/timetable/future/{userId}', [TimetableController::class, 'getFutureClasses']);

// --- Course API ---
Route::get('/courses', [CourseController::class, 'apiGetAllCourses'])->middleware(['api.auth', 'role:admin']);
Route::get('/courses/{courseId}', [CourseController::class, 'apiGetCourse'])->middleware(['api.auth', 'role:admin']);
Route::post('/courses', [CourseController::class, 'apiCreateCourse'])->middleware(['api.auth', 'role:admin']);
Route::put('/courses/{courseId}', [CourseController::class, 'apiUpdateCourse'])->middleware(['api.auth', 'role:admin']);

// --- Course Subject API ---
Route::get('/courseSubject', [CourseSubjectController::class, 'apiGetAllCourseSubjects'])->middleware(['api.auth', 'role:admin']);
Route::get('/courseSubject/{courseSubjectId}', [CourseSubjectController::class, 'getCourseSubject'])->middleware(['api.auth', 'role:admin']);
Route::post('/courseSubject', [CourseSubjectController::class, 'apiCreateCourseSubject'])->middleware(['api.auth', 'role:admin']);
Route::put('/courseSubject/{id}', [CourseSubjectController::class, 'apiUpdateCourseSubject'])->middleware(['api.auth', 'role:admin']);
Route::get('/courseSubject/fetchCourseSubjectWithForeignKeys', [CourseSubjectController::class, 'fetchCourseSubjectWithForeignKeys'])->middleware('api.auth');
Route::get('/courseSubjects/byCourse/{courseId}', [CourseSubjectController::class, 'getCourseSubjectsByCourse'])->middleware('api.auth');

// --- Subjects API ---
Route::get('/subjects', [SubjectController::class, 'apiGetAllSubjects'])->middleware(['api.auth', 'role:admin']);
Route::get('/subjects/{subjectId}', [SubjectController::class, 'apiGetSubject'])->middleware(['api.auth', 'role:admin']);
Route::post('/subjects', [SubjectController::class, 'apiCreateSubject'])->middleware(['api.auth', 'role:admin']);
Route::put('/subjects/{id}', [SubjectController::class, 'apiUpdateSubject'])->middleware(['api.auth', 'role:admin']);
Route::get('/subject/byCourseSubject/{courseSubjectId}', [SubjectController::class, 'getSubjectByCourseSubject'])->middleware('api.auth');

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
Route::prefix('replacement')->group(function () {
    // Appeals.
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
