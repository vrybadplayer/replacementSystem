<?php

namespace App\Http\Controllers;

use App\Models\CourseSubject;

abstract class CourseSubjectController extends Controller
{

    // Temporary Function
    public function getAllCourseSubjects() {
        return response()->json(CourseSubject::all());
    }
}
