<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClassGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $groups = [
            ['classGroupId' => 'CS001', 'maxCapacity' => 300, 'currentEnrollment' => 120],
            ['classGroupId' => 'CS002', 'maxCapacity' => 300, 'currentEnrollment' => 100],
            ['classGroupId' => 'CS003', 'maxCapacity' => 300, 'currentEnrollment' => 90],
            ['classGroupId' => 'CS004', 'maxCapacity' => 30,  'currentEnrollment' => 22],
            ['classGroupId' => 'CS005', 'maxCapacity' => 30,  'currentEnrollment' => 18],
            ['classGroupId' => 'CS006', 'maxCapacity' => 30,  'currentEnrollment' => 25],
            ['classGroupId' => 'CS007', 'maxCapacity' => 30,  'currentEnrollment' => 12],
            ['classGroupId' => 'CS008', 'maxCapacity' => 30,  'currentEnrollment' => 15],
            ['classGroupId' => 'CS009', 'maxCapacity' => 30,  'currentEnrollment' => 30],
            ['classGroupId' => 'CS010', 'maxCapacity' => 30,  'currentEnrollment' => 20],
            ['classGroupId' => 'CS011', 'maxCapacity' => 30,  'currentEnrollment' => 14],
            ['classGroupId' => 'CS012', 'maxCapacity' => 30,  'currentEnrollment' => 28],
            ['classGroupId' => 'CS013', 'maxCapacity' => 300, 'currentEnrollment' => 85],
            ['classGroupId' => 'CS014', 'maxCapacity' => 30,  'currentEnrollment' => 16],
            ['classGroupId' => 'CS015', 'maxCapacity' => 30,  'currentEnrollment' => 10],
            ['classGroupId' => 'CS016', 'maxCapacity' => 30,  'currentEnrollment' => 11],
            ['classGroupId' => 'CS017', 'maxCapacity' => 300, 'currentEnrollment' => 110],
            ['classGroupId' => 'CS018', 'maxCapacity' => 30,  'currentEnrollment' => 26],
            ['classGroupId' => 'CS019', 'maxCapacity' => 30,  'currentEnrollment' => 21],
            ['classGroupId' => 'CS020', 'maxCapacity' => 30,  'currentEnrollment' => 13],
        ];

        // Add timestamps to all records
        foreach ($groups as &$group) {
            $group['created_at'] = $now;
            $group['updated_at'] = $now;
        }

        DB::table('classGroup')->insert($groups);
    }
}