<?php

namespace App\Http\Controllers;

use App\Facades\CourseSubjectManagementFacade;
use App\Models\Course;

class CourseController extends Controller
{
    /**
     * Step 1-4: Fetch from API → Process → Format → Return View
     */
    public function index()
    {
        // Step 1-3: Fetch from API, process data, format for views (through Facade/Service)
        $courses = CourseSubjectManagementFacade::getCoursesForViewFromApi();

        // Step 4: Return view with processed data
        return view('course.listCourse', compact('courses'));
    }

    // ---------- API TEST ----------
    public function apiIndex()
    {
        // TODO: Fetch properly
        $dummyCourses = [
            ['courseId' => 'RSD', 'courseName' => 'Software Engineering'],
            ['courseId' => 'RIS', 'courseName' => 'Information Systems'],
            ['courseId' => 'RIT', 'courseName' => 'Internet Technology'],
        ];

        return response()->json([
            'success' => true,
            'data' => $dummyCourses,
        ]);
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

