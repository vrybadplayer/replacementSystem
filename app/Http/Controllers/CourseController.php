<?php

namespace App\Http\Controllers;

use App\Facades\CourseFacade;

class CourseController extends Controller
{
    public function index()
    {
        $courses = CourseFacade::getCoursesForView();
        return view('course.listCourse', compact('courses'));
    }
}

