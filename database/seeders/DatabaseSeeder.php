<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Subject;
use App\Models\CourseSubject;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            VenueSeeder::class,
            ClassGroupSeeder::class,
        ]);
        

        // We create U001 - U020 to match the IDs used in your TimetableSeeder
        $this->command->info('Seeding Users...');
        
        // Admin (U001)
        User::factory()->admin()->create([
            'userId' => 'U001', 
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Lecturer (U002 - U010)
        for ($i = 2; $i <= 10; $i++) {
            $id = 'U' . str_pad($i, 3, '0', STR_PAD_LEFT); // Generates U002, U003...
            User::factory()->lecturer()->create([
                'userId' => $id,
                'email' => "lecturer{$i}@example.com",
            ]);
        }

        // Students (U0011 - U020)
        $groups = ['CG001', 'CG002', 'CG003', 'CG004', 'CG005', 'CG006'];
        for ($i = 11; $i <= 20; $i++) {
            $id = 'U' . str_pad($i, 3, '0', STR_PAD_LEFT);
            
            $assignedGroup = $groups[$i % 6]; 

            User::factory()->student()->create([
                'userId' => $id,
                'email' => "student{$i}@example.com",
                'classGroupId' => $assignedGroup,
            ]);
        }

        $this->command->info('Seeding Courses, Subjects, and Course Subjects...');
        $this->call([
            CourseSeeder::class,
            SubjectSeeder::class,
            CourseSubjectSeeder::class,
        ]);

        // Timetable depends on User, Venue, ClassGroup, and CourseSubject existing first.
        $this->call([
            TimetableSeeder::class,
        ]);

        // Course Enrollment - Enroll all students in courses
        $this->call([
            CourseEnrollmentSeeder::class,
        ]);
        
        $this->command->info('All seeders completed successfully!');
    }
}
