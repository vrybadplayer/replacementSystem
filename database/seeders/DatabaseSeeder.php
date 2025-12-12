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
        // 1. Create Users
        // We create U001 - U020 to match the IDs used in your TimetableSeeder
        $this->command->info('Seeding Users...');
        
        // Admin (U001)
        User::factory()->admin()->create([
            'userId' => 'U001', 
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Students (U002 - U010)
        for ($i = 2; $i <= 10; $i++) {
            $id = 'U' . str_pad($i, 3, '0', STR_PAD_LEFT); // Generates U002, U003...
            User::factory()->student()->create([
                'userId' => $id,
                'email' => "student{$i}@example.com",
            ]);
        }


        // 2. Call Independent External Seeders
        // Venues and ClassGroups don't depend on anything, so we run them now.
        $this->call([
            VenueSeeder::class,
            ClassGroupSeeder::class,
        ]);


        // 3. Create Courses & Subjects (Inline)
        // These are required to create CourseSubjects
        $this->command->info('Seeding Courses and Subjects...');
        
        Course::create(['courseId' => 'RSD', 'courseName' => 'Bachelor of Software Engineering']);
        Course::create(['courseId' => 'RIS', 'courseName' => 'Bachelor of Information Systems']);

        Subject::create(['subjectId' => 'BMIT3173', 'subjectName' => 'Integrative Programming', 'creditHours' => 3]);
        Subject::create(['subjectId' => 'BJEL2013', 'subjectName' => 'English for Academic Communication', 'creditHours' => 3]);
        Subject::create(['subjectId' => 'BACS2053', 'subjectName' => 'Database Management', 'creditHours' => 3]);
        Subject::create(['subjectId' => 'BMCS3413', 'subjectName' => 'Network Security', 'creditHours' => 3]);
        Subject::create(['subjectId' => 'MPU-3133', 'subjectName' => 'Philosophy', 'creditHours' => 3]);
        Subject::create(['subjectId' => 'MPU-3302', 'subjectName' => 'Integrity', 'creditHours' => 3]);


        // 4. Create Course-Subject Relationships (Inline)
        // The IDs here MUST match what is in your TimetableSeeder (e.g., BMIT3173, etc)
        // Note: In your SQL dump, the timetable table used subject codes as courseSubjectId.
        CourseSubject::create(['courseSubjectId' => 'BMIT3173', 'courseId' => 'RSD', 'subjectId' => 'BMIT3173']);
        CourseSubject::create(['courseSubjectId' => 'BJEL2013', 'courseId' => 'RSD', 'subjectId' => 'BJEL2013']);
        CourseSubject::create(['courseSubjectId' => 'BACS2053', 'courseId' => 'RSD', 'subjectId' => 'BACS2053']);
        CourseSubject::create(['courseSubjectId' => 'BMCS3413', 'courseId' => 'RSD', 'subjectId' => 'BMCS3413']);
        CourseSubject::create(['courseSubjectId' => 'MPU-3133', 'courseId' => 'RSD', 'subjectId' => 'MPU-3133']);
        CourseSubject::create(['courseSubjectId' => 'MPU-3302', 'courseId' => 'RSD', 'subjectId' => 'MPU-3302']);


        // 5. Call Dependent External Seeder
        // Timetable depends on User, Venue, ClassGroup, and CourseSubject existing first.
        $this->call([
            TimetableSeeder::class,
        ]);
        
        $this->command->info('All seeders completed successfully!');
    }
}