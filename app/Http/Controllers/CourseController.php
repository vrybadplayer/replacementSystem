<?php

namespace App\Http\Controllers;

use App\Facades\CourseManagementFacade;

class CourseController extends Controller
{
    /**
     * Step 1-4: Fetch from API → Process → Format → Return View
     */
    public function index()
    {
        // Step 1-3: Fetch from API, process data, format for views (through Facade/Service)
        $courses = CourseManagementFacade::getCoursesForViewFromApi();

        // Step 4: Return view with processed data
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

