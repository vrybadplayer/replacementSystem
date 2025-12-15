<?php

namespace Database\Seeders;

use App\Facades\CourseSubjectManagementFacade;
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
                'courseCode' => 'RSD',
                'courseName' => 'Bachelor of Information Technology (Honours) – Software Systems Development',
                'faculty' => 'FOCS',
                'status' => 'active',
            ],
            [
                'courseId' => 'C0002',
                'courseCode' => 'RIS',
                'courseName' => 'Bachelor of Information Technology (Honours) – Information Security',
                'faculty' => 'FOCS',
                'status' => 'active',
            ],
            [
                'courseId' => 'C0003',
                'courseCode' => 'RIT',
                'courseName' => 'Diploma in Information Technology',
                'faculty' => 'FOCS',
                'status' => 'active',
            ],
            [
                'courseId' => 'C0004',
                'courseCode' => 'BIS',
                'courseName' => 'Bachelor of Information Systems (Honours)',
                'faculty' => 'FOIT',
                'status' => 'active',
            ],
            [
                'courseId' => 'C0005',
                'courseCode' => 'BCS',
                'courseName' => 'Bachelor of Computer Science (Honours)',
                'faculty' => 'FOCS',
                'status' => 'active',
            ],
        ];        

        foreach ($courses as $course) {
            CourseSubjectManagementFacade::createOrUpdateCourse($course, $course['courseId']);
        }
    }
}

