<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\StudentTimetableController;
use App\Http\Controllers\AdminTimetableController;


// This file is what ApiService files will call

// Timetable Module API
Route::get('/', [StudentTimetableController::class, '']);
Route::get('/testing', [StudentTimetableController::class, 'test']);


// --- Course Management API ---
Route::get('/courses', [CourseController::class, 'apiIndex']);
Route::get('/courses2', [CourseController::class, 'apiIndex2']);
Route::get('/subjects', [SubjectController::class, 'apiIndex']);


// --- User Management API ---
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- Replacement Module API ---