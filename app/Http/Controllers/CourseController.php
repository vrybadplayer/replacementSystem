<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        // Hardcoded course data - In a real app, this would come from the database
        $courses = [
            [
                'id' => 1,
                'code' => 'CSIT101',
                'name' => 'Introduction to Computer Science',
                'credits' => 3,
                'department' => 'Computer Science',
                'status' => 'active'
            ],
            [
                'id' => 2,
                'code' => 'CSIT201',
                'name' => 'Data Structures and Algorithms',
                'credits' => 4,
                'department' => 'Computer Science',
                'status' => 'active'
            ],
            [
                'id' => 3,
                'code' => 'BMIT3173',
                'name' => 'Integrative Programming',
                'credits' => 3,
                'department' => 'Information Technology',
                'status' => 'active'
            ],
            [
                'id' => 4,
                'code' => 'BJEL2013',
                'name' => 'English Language',
                'credits' => 2,
                'department' => 'Languages',
                'status' => 'active'
            ],
        ];

        return view('course.listCourse', compact('courses'));
    }
}

