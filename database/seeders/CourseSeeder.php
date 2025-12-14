<?php

namespace Database\Seeders;

use App\Facades\CourseManagementFacade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'courseId' => 'C0001',
                'courseCode' => 'CSIT101',
                'courseName' => 'Introduction to Computer Science',
                'faculty' => 'FOCS',
                'status' => 'active',
            ],
            [
                'courseId' => 'C0002',
                'courseCode' => 'CSIT201',
                'courseName' => 'Data Structures and Algorithms',
                'faculty' => 'FOCS',
                'status' => 'active',
            ],
            [
                'courseId' => 'C0003',
                'courseCode' => 'BMIT3173',
                'courseName' => 'Integrative Programming',
                'faculty' => 'FOIT',
                'status' => 'active',
            ],
            [
                'courseId' => 'C0004',
                'courseCode' => 'RSD',
                'courseName' => 'Bachelor of Software Engineering',
                'faculty' => 'FOCS',
                'status' => 'active',
            ],
            [
                'courseId' => 'C0005',
                'courseCode' => 'RIS',
                'courseName' => 'Bachelor of Information Systems',
                'faculty' => 'FOIT',
                'status' => 'active',
            ],
        ];

        foreach ($courses as $course) {
            CourseManagementFacade::createOrUpdateCourse($course, $course['courseId']);
        }
    }
}

