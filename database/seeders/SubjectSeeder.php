<?php

namespace Database\Seeders;

use App\Facades\SubjectFacade;
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
                'subjectId' => 'SUB001',
                'subjectCode' => 'BMIT3173',
                'subjectName' => 'Integrative Programming',
                'creditHours' => 3,
                'category' => 'Core',
                'status' => 'active',
            ],
            [
                'subjectId' => 'SUB002',
                'subjectCode' => 'BJEL2013',
                'subjectName' => 'English Language',
                'creditHours' => 2,
                'category' => 'General',
                'status' => 'active',
            ],
            [
                'subjectId' => 'SUB003',
                'subjectCode' => 'CSIT301',
                'subjectName' => 'Database Systems',
                'creditHours' => 3,
                'category' => 'Core',
                'status' => 'active',
            ],
            [
                'subjectId' => 'SUB004',
                'subjectCode' => 'CSIT302',
                'subjectName' => 'Web Development',
                'creditHours' => 3,
                'category' => 'Elective',
                'status' => 'active',
            ],
            [
                'subjectId' => 'BMIT3173',
                'subjectCode' => 'BMIT3173',
                'subjectName' => 'Integrative Programming',
                'creditHours' => 3,
                'category' => 'Core',
                'status' => 'active',
            ],
            [
                'subjectId' => 'BJEL2013',
                'subjectCode' => 'BJEL2013',
                'subjectName' => 'English for Academic Communication',
                'creditHours' => 3,
                'category' => 'General',
                'status' => 'active',
            ],
            [
                'subjectId' => 'BACS2053',
                'subjectCode' => 'BACS2053',
                'subjectName' => 'Database Management',
                'creditHours' => 3,
                'category' => 'Core',
                'status' => 'active',
            ],
        ];

        foreach ($subjects as $subject) {
            SubjectFacade::createOrUpdateSubject($subject, $subject['subjectId']);
        }
    }
}

