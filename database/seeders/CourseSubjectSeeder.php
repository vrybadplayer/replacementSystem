<?php

namespace Database\Seeders;

use App\Facades\CourseSubjectFacade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courseSubjects = [
            [
                'courseSubjectId' => 'CS001',
                'courseId' => 'IT001',
                'subjectId' => 'SUB001',
            ],
            [
                'courseSubjectId' => 'CS002',
                'courseId' => 'IT001',
                'subjectId' => 'SUB002',
            ],
            [
                'courseSubjectId' => 'CS003',
                'courseId' => 'CS001',
                'subjectId' => 'SUB003',
            ],
            [
                'courseSubjectId' => 'CS004',
                'courseId' => 'CS001',
                'subjectId' => 'SUB004',
            ],
            [
                'courseSubjectId' => 'CS005',
                'courseId' => 'CS002',
                'subjectId' => 'SUB003',
            ],
            [
                'courseSubjectId' => 'CS006',
                'courseId' => 'RSD',
                'subjectId' => 'BMIT3173',
            ],
            [
                'courseSubjectId' => 'CS007',
                'courseId' => 'RSD',
                'subjectId' => 'BJEL2013',
            ],
            [
                'courseSubjectId' => 'CS008',
                'courseId' => 'RSD',
                'subjectId' => 'BACS2053',
            ],
        ];

        foreach ($courseSubjects as $courseSubject) {
            CourseSubjectFacade::createOrUpdateCourseSubject($courseSubject, $courseSubject['courseSubjectId']);
        }
    }
}

