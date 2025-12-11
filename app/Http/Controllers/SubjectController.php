<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        // Hardcoded subject data - In a real app, this would come from the database
        $subjects = [
            [
                'id' => 1,
                'code' => 'BMIT3173',
                'name' => 'Integrative Programming',
                'credits' => 3,
                'course' => 'CSIT101',
                'lecturer' => 'Malarvili',
                'type' => 'Lecture',
                'status' => 'active'
            ],
            [
                'id' => 2,
                'code' => 'BJEL2013',
                'name' => 'English Language',
                'credits' => 2,
                'course' => 'CSIT101',
                'lecturer' => 'Christopher Quah',
                'type' => 'Tutorial',
                'status' => 'active'
            ],
            [
                'id' => 3,
                'code' => 'CSIT301',
                'name' => 'Database Systems',
                'credits' => 3,
                'course' => 'CSIT201',
                'lecturer' => 'Dr. Smith',
                'type' => 'Lecture',
                'status' => 'active'
            ],
            [
                'id' => 4,
                'code' => 'CSIT302',
                'name' => 'Web Development',
                'credits' => 3,
                'course' => 'CSIT201',
                'lecturer' => 'Dr. Johnson',
                'type' => 'Lab',
                'status' => 'active'
            ],
        ];

        return view('subject.listSubject', compact('subjects'));
    }
}

