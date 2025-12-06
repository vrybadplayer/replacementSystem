<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimetableController;

// Route to the timetable page
Route::get('/student/timetable', [TimetableController::class, 'index'])->name('student.timetable');

// Mock route for where the user goes after clicking a class
Route::get('/replacement/appeal', function() {
    return "This is the Replacement Appeal Page (Out of Scope for this task)";
})->name('replacement.appeal');