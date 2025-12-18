<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseEnrollmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students (users with role = 'student')
        $students = User::where('role', 'student')->get();

        // Get all active courses
        $courses = Course::where('status', 'active')->get();

        $this->command->info('Seeding Course Enrollments...');

        $enrollmentCounter = 1;
        $courseIndex = 0;

        foreach ($students as $student) {
            // Assign each student to only one course (round-robin distribution)
            $course = $courses[$courseIndex % count($courses)];
            
            // Generate sequential enrollment ID (CE001, CE002, etc.)
            $enrollmentID = 'CE' . str_pad($enrollmentCounter, 3, '0', STR_PAD_LEFT);

            // Check if enrollment already exists to avoid duplicates
            $exists = CourseEnrollment::where('userID', $student->userId)
                ->where('courseID', $course->courseId)
                ->exists();

            if (!$exists) {
                CourseEnrollment::create([
                    'enrollmentID' => $enrollmentID,
                    'userID' => $student->userId,
                    'courseID' => $course->courseId,
                    'year' => rand(1, 3),
                    'semester' => rand(1, 3),
                ]);

                $this->command->info("Enrolled {$student->userId} in {$course->courseCode} with ID {$enrollmentID}");
            }

            $enrollmentCounter++;
            $courseIndex++;
        }

        $this->command->info('Course Enrollment seeding completed!');
    }
}
