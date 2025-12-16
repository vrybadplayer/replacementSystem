<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courseSubjects = [
            // Course C0001 (Introduction to Computer Science) - 6 subjects
            [
                'courseSubjectId' => 'CS0001',
                'courseId' => 'C0001',
                'subjectId' => 'S0006',
            ],
            [
                'courseSubjectId' => 'CS0002',
                'courseId' => 'C0001',
                'subjectId' => 'S0007',
            ],
            [
                'courseSubjectId' => 'CS0003',
                'courseId' => 'C0001',
                'subjectId' => 'S0001',
            ],
            [
                'courseSubjectId' => 'CS0004',
                'courseId' => 'C0001',
                'subjectId' => 'S0002',
            ],
            [
                'courseSubjectId' => 'CS0005',
                'courseId' => 'C0001',
                'subjectId' => 'S0010',
            ],
            [
                'courseSubjectId' => 'CS0006',
                'courseId' => 'C0001',
                'subjectId' => 'S0003',
            ],
            
            // Course C0002 (Data Structures and Algorithms) - 6 subjects
            [
                'courseSubjectId' => 'CS0007',
                'courseId' => 'C0002',
                'subjectId' => 'S0007',
            ],
            [
                'courseSubjectId' => 'CS0008',
                'courseId' => 'C0002',
                'subjectId' => 'S0004',
            ],
            [
                'courseSubjectId' => 'CS0009',
                'courseId' => 'C0002',
                'subjectId' => 'S0005',
            ],
            [
                'courseSubjectId' => 'CS0010',
                'courseId' => 'C0002',
                'subjectId' => 'S0008',
            ],
            [
                'courseSubjectId' => 'CS0011',
                'courseId' => 'C0002',
                'subjectId' => 'S0009',
            ],
            [
                'courseSubjectId' => 'CS0012',
                'courseId' => 'C0002',
                'subjectId' => 'S0010',
            ],
            
            // Course C0003 (Integrative Programming) - 6 subjects
            [
                'courseSubjectId' => 'CS0013',
                'courseId' => 'C0003',
                'subjectId' => 'S0001',
            ],
            [
                'courseSubjectId' => 'CS0014',
                'courseId' => 'C0003',
                'subjectId' => 'S0003',
            ],
            [
                'courseSubjectId' => 'CS0015',
                'courseId' => 'C0003',
                'subjectId' => 'S0004',
            ],
            [
                'courseSubjectId' => 'CS0016',
                'courseId' => 'C0003',
                'subjectId' => 'S0005',
            ],
            [
                'courseSubjectId' => 'CS0017',
                'courseId' => 'C0003',
                'subjectId' => 'S0002',
            ],
            [
                'courseSubjectId' => 'CS0018',
                'courseId' => 'C0003',
                'subjectId' => 'S0006',
            ],
            
            // Course C0004 (Bachelor of Software Engineering) - 6 subjects
            [
                'courseSubjectId' => 'CS0019',
                'courseId' => 'C0004',
                'subjectId' => 'S0001',
            ],
            [
                'courseSubjectId' => 'CS0020',
                'courseId' => 'C0004',
                'subjectId' => 'S0002',
            ],
            [
                'courseSubjectId' => 'CS0021',
                'courseId' => 'C0004',
                'subjectId' => 'S0003',
            ],
            [
                'courseSubjectId' => 'CS0022',
                'courseId' => 'C0004',
                'subjectId' => 'S0008',
            ],
            [
                'courseSubjectId' => 'CS0023',
                'courseId' => 'C0004',
                'subjectId' => 'S0005',
            ],
            [
                'courseSubjectId' => 'CS0024',
                'courseId' => 'C0004',
                'subjectId' => 'S0009',
            ],
            
            // Course C0005 (Bachelor of Information Systems) - 6 subjects
            [
                'courseSubjectId' => 'CS0025',
                'courseId' => 'C0005',
                'subjectId' => 'S0003',
            ],
            [
                'courseSubjectId' => 'CS0026',
                'courseId' => 'C0005',
                'subjectId' => 'S0004',
            ],
            [
                'courseSubjectId' => 'CS0027',
                'courseId' => 'C0005',
                'subjectId' => 'S0005',
            ],
            [
                'courseSubjectId' => 'CS0028',
                'courseId' => 'C0005',
                'subjectId' => 'S0002',
            ],
            [
                'courseSubjectId' => 'CS0029',
                'courseId' => 'C0005',
                'subjectId' => 'S0001',
            ],
            [
                'courseSubjectId' => 'CS0030',
                'courseId' => 'C0005',
                'subjectId' => 'S0008',
            ],
        ];

        DB::table('course_subject')->insert($courseSubjects);
    }
}

