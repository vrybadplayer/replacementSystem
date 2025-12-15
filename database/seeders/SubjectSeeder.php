<?php

namespace Database\Seeders;

use App\Facades\CourseSubjectManagementFacade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                'subjectId' => 'S0001',
                'subjectCode' => 'BMIT3173',
                'subjectName' => 'Integrative Programming',
                'creditHours' => 3,
                'category' => 'Core',
                'status' => 'active',
            ],
            [
                'subjectId' => 'S0002',
                'subjectCode' => 'BJEL2013',
                'subjectName' => 'English for Academic Communication',
                'creditHours' => 3,
                'category' => 'General',
                'status' => 'active',
            ],
            [
                'subjectId' => 'S0003',
                'subjectCode' => 'BACS2053',
                'subjectName' => 'Database Management',
                'creditHours' => 3,
                'category' => 'Core',
                'status' => 'active',
            ],
            [
                'subjectId' => 'S0004',
                'subjectCode' => 'CSIT301',
                'subjectName' => 'Database Systems',
                'creditHours' => 3,
                'category' => 'Core',
                'status' => 'active',
            ],
            [
                'subjectId' => 'S0005',
                'subjectCode' => 'CSIT302',
                'subjectName' => 'Web Development',
                'creditHours' => 3,
                'category' => 'Elective',
                'status' => 'active',
            ],
            [
                'subjectId' => 'S0006',
                'subjectCode' => 'CSIT101',
                'subjectName' => 'Introduction to Computer Science',
                'creditHours' => 3,
                'category' => 'Core',
                'status' => 'active',
            ],
            [
                'subjectId' => 'S0007',
                'subjectCode' => 'CSIT201',
                'subjectName' => 'Data Structures and Algorithms',
                'creditHours' => 4,
                'category' => 'Core',
                'status' => 'active',
            ],
            [
                'subjectId' => 'S0008',
                'subjectCode' => 'CSIT303',
                'subjectName' => 'Software Engineering',
                'creditHours' => 3,
                'category' => 'Core',
                'status' => 'active',
            ],
            [
                'subjectId' => 'S0009',
                'subjectCode' => 'CSIT306',
                'subjectName' => 'Artificial Intelligence',
                'creditHours' => 3,
                'category' => 'Elective',
                'status' => 'active',
            ],
            [
                'subjectId' => 'S0010',
                'subjectCode' => 'MATH101',
                'subjectName' => 'Discrete Mathematics',
                'creditHours' => 3,
                'category' => 'General',
                'status' => 'active',
            ],
        ];

        foreach ($subjects as $subject) {
            CourseSubjectManagementFacade::createOrUpdateSubject($subject, $subject['subjectId']);
        }
    }
}

