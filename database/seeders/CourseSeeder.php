<?php

namespace Database\Seeders;

use App\Facades\CourseFacade;
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
                'courseId' => 'CS001',
                'courseCode' => 'CSIT101',
                'courseName' => 'Introduction to Computer Science',
                'faculty' => 'FOCS',
                'status' => 'active',
            ],
            [
                'courseId' => 'CS002',
                'courseCode' => 'CSIT201',
                'courseName' => 'Data Structures and Algorithms',
                'faculty' => 'FOCS',
                'status' => 'active',
            ],
            [
                'courseId' => 'IT001',
                'courseCode' => 'BMIT3173',
                'courseName' => 'Integrative Programming',
                'faculty' => 'FOIT',
                'status' => 'active',
            ],
            [
                'courseId' => 'LANG001',
                'courseCode' => 'BJEL2013',
                'courseName' => 'English Language',
                'faculty' => 'FOL',
                'status' => 'active',
            ],
            [
                'courseId' => 'RSD',
                'courseCode' => 'RSD',
                'courseName' => 'Bachelor of Software Engineering',
                'faculty' => 'FOCS',
                'status' => 'active',
            ],
            [
                'courseId' => 'RIS',
                'courseCode' => 'RIS',
                'courseName' => 'Bachelor of Information Systems',
                'faculty' => 'FOIT',
                'status' => 'active',
            ],
        ];

        foreach ($courses as $course) {
            CourseFacade::createOrUpdateCourse($course, $course['courseId']);
        }
    }
}

