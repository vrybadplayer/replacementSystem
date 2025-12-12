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


        // 3. Seed Courses, Subjects, and Course Subjects using Facades
        $this->command->info('Seeding Courses, Subjects, and Course Subjects...');
        $this->call([
            CourseSeeder::class,
            SubjectSeeder::class,
            CourseSubjectSeeder::class,
        ]);

        // 4. Call Dependent External Seeder
        // Timetable depends on User, Venue, ClassGroup, and CourseSubject existing first.
        $this->call([
            TimetableSeeder::class,
        ]);
        
        $this->command->info('All seeders completed successfully!');
    }
}
