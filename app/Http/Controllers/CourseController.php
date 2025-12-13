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

    // ---------- API TEST ----------
    public function apiIndex()
    {
        // Option A: If you have seeded database data
        // return response()->json(Course::all());

        // Option B: Hardcoded Dummy Data (Safe for testing right now)
        $dummyCourses = [
            ['courseId' => 'RSD', 'courseName' => 'Software Engineering'],
            ['courseId' => 'RIS', 'courseName' => 'Information Systems'],
            ['courseId' => 'RIT', 'courseName' => 'Internet Technology'],
        ];

        return response()->json($dummyCourses);
    }

        public function apiIndex2()
    {
        $dummyCourses = [
            ['courseId' => 'AAA', 'courseName' => 'COURSE AAA'],
            ['courseId' => 'BBB', 'courseName' => 'COURSE BBB'],
            ['courseId' => 'CCC', 'courseName' => 'COURSE CCC'],
        ];

        return response()->json($dummyCourses);
    }
}

