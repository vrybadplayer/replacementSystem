<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimetableController;

// Timetable module (Student)
Route::get('/student/timetable', [TimetableController::class, 'index'])->name('student.timetable');
Route::get('/student/replacementOptions', [TimetableController::class, 'viewOptions'])->name('student.replacecmentOptions');
Route::get('/student/selectTimeslot', [TimetableController::class, 'selectTimeslot'])->name('student.selectTimeslot');

// Mock route for where the user goes after clicking a class
Route::get('/replacement/appeal', function() {
    return "This is the Replacement Appeal Page (Out of Scope for this task)";
})->name('replacement.appeal');