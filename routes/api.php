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

// --- Replacement Module API ---