<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Venue;
use App\Models\Course;
use App\Models\Subject;
use App\Models\ClassGroup;
use App\Models\CourseSubject;
use App\Models\Timetable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'userId' => 'U0001',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create Admin User
        User::factory()->admin()->create([
            'userId' => 'U0002',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create Student User
        User::factory()->student()->create([
            'userId' => 'U0003',
            'name' => 'Student User',
            'email' => 'student@example.com',
        ]);

        // Create Student User
        User::factory()->lecturer()->create([
            'userId' => 'U0004',
            'name' => 'Lecturer User',
            'email' => 'lecturer@example.com',
        ]);

        // Create additional students for testing
        User::factory()->student()->count(5)->create();

        // ===== Replacement Module Sample Data =====
        $adminUser = User::where('email', 'admin@example.com')->first();
        
        // Create Venues
        Venue::create(['venueId' => 'DK4', 'venueType' => 'Lecture Hall', 'pax' => 50]);
        Venue::create(['venueId' => 'K303', 'venueType' => 'Tutorial Room', 'pax' => 30]);
        Venue::create(['venueId' => 'D203F', 'venueType' => 'Lab', 'pax' => 35]);

        // Create Courses
        Course::create(['courseId' => 'RSD', 'courseName' => 'Bachelor of Software Engineering']);
        Course::create(['courseId' => 'RIS', 'courseName' => 'Bachelor of Information Systems']);

        // Create Subjects
        Subject::create(['subjectId' => 'BMIT3173', 'subjectName' => 'Integrative Programming', 'creditHours' => 3]);
        Subject::create(['subjectId' => 'BJEL2013', 'subjectName' => 'English for Academic Communication', 'creditHours' => 3]);
        Subject::create(['subjectId' => 'BACS2053', 'subjectName' => 'Database Management', 'creditHours' => 3]);

        // Create Class Groups
        ClassGroup::create(['classGroupId' => 'G1', 'maxCapacity' => 30, 'currentEnrollment' => 25]);
        ClassGroup::create(['classGroupId' => 'G2', 'maxCapacity' => 30, 'currentEnrollment' => 28]);
        ClassGroup::create(['classGroupId' => 'G3', 'maxCapacity' => 30, 'currentEnrollment' => 20]);
        ClassGroup::create(['classGroupId' => 'G4', 'maxCapacity' => 30, 'currentEnrollment' => 22]);

        // Create Course-Subject relationships
        CourseSubject::create(['courseSubjectId' => 'CS001', 'courseId' => 'RSD', 'subjectId' => 'BMIT3173']);
        CourseSubject::create(['courseSubjectId' => 'CS002', 'courseId' => 'RSD', 'subjectId' => 'BJEL2013']);
        CourseSubject::create(['courseSubjectId' => 'CS003', 'courseId' => 'RSD', 'subjectId' => 'BACS2053']);

        // Create Timetable entries (upcoming classes for replacement)
        Timetable::create([
            'timetableId' => 'TT001',
            'classGroupId' => 'G1',
            'courseSubjectId' => 'CS001',
            'classType' => 'Lecture',
            'userId' => $adminUser->userId,
            'venueId' => 'DK4',
            'dayOfWeek' => 'Monday',
            'startTime' => '08:00:00',
            'endTime' => '10:00:00',
            'classDate' => '2025-12-22',
            'classMode' => 'Physical',
        ]);

        Timetable::create([
            'timetableId' => 'TT002',
            'classGroupId' => 'G2',
            'courseSubjectId' => 'CS001',
            'classType' => 'Lecture',
            'userId' => $adminUser->userId,
            'venueId' => 'DK4',
            'dayOfWeek' => 'Thursday',
            'startTime' => '10:00:00',
            'endTime' => '12:00:00',
            'classDate' => '2025-12-25',
            'classMode' => 'Physical',
        ]);

        Timetable::create([
            'timetableId' => 'TT003',
            'classGroupId' => 'G1',
            'courseSubjectId' => 'CS002',
            'classType' => 'Tutorial',
            'userId' => $adminUser->userId,
            'venueId' => 'K303',
            'dayOfWeek' => 'Tuesday',
            'startTime' => '14:00:00',
            'endTime' => '16:00:00',
            'classDate' => '2025-12-23',
            'classMode' => 'Physical',
        ]);

        Timetable::create([
            'timetableId' => 'TT004',
            'classGroupId' => 'G3',
            'courseSubjectId' => 'CS003',
            'classType' => 'Lab',
            'userId' => $adminUser->userId,
            'venueId' => 'D203F',
            'dayOfWeek' => 'Wednesday',
            'startTime' => '09:00:00',
            'endTime' => '11:00:00',
            'classDate' => '2025-12-24',
            'classMode' => 'Physical',
        ]);

        Timetable::create([
            'timetableId' => 'TT005',
            'classGroupId' => 'G2',
            'courseSubjectId' => 'CS001',
            'classType' => 'Lecture',
            'userId' => $adminUser->userId,
            'venueId' => 'DK4',
            'dayOfWeek' => 'Friday',
            'startTime' => '15:00:00',
            'endTime' => '17:00:00',
            'classDate' => '2025-12-27',
            'classMode' => 'Physical',
        ]);

        $this->command->info('Replacement module data seeded successfully!');
    }
}
