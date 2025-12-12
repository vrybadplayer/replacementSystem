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
            ['classGroupId' => 'CG001', 'maxCapacity' => 30,  'currentEnrollment' => 19],
            ['classGroupId' => 'CG002', 'maxCapacity' => 30,  'currentEnrollment' => 20],
            ['classGroupId' => 'CG003', 'maxCapacity' => 30,  'currentEnrollment' => 30],
            ['classGroupId' => 'CG004', 'maxCapacity' => 30,  'currentEnrollment' => 22],
            ['classGroupId' => 'CG005', 'maxCapacity' => 30,  'currentEnrollment' => 18],
            ['classGroupId' => 'CG006', 'maxCapacity' => 30,  'currentEnrollment' => 25],
            ['classGroupId' => 'CG007', 'maxCapacity' => 30,  'currentEnrollment' => 12],
            ['classGroupId' => 'CG008', 'maxCapacity' => 30,  'currentEnrollment' => 15],
            ['classGroupId' => 'CG009', 'maxCapacity' => 30,  'currentEnrollment' => 30],
            ['classGroupId' => 'CG010', 'maxCapacity' => 30,  'currentEnrollment' => 20],
            ['classGroupId' => 'CG011', 'maxCapacity' => 30,  'currentEnrollment' => 14],
            ['classGroupId' => 'CG012', 'maxCapacity' => 30,  'currentEnrollment' => 28],
            ['classGroupId' => 'CG013', 'maxCapacity' => 30,  'currentEnrollment' => 25],
            ['classGroupId' => 'CG014', 'maxCapacity' => 30,  'currentEnrollment' => 16],
            ['classGroupId' => 'CG015', 'maxCapacity' => 30,  'currentEnrollment' => 19],
            ['classGroupId' => 'CG016', 'maxCapacity' => 30,  'currentEnrollment' => 17],
            ['classGroupId' => 'CG017', 'maxCapacity' => 30,  'currentEnrollment' => 24],
            ['classGroupId' => 'CG018', 'maxCapacity' => 30,  'currentEnrollment' => 26],
            ['classGroupId' => 'CG019', 'maxCapacity' => 30,  'currentEnrollment' => 21],
            ['classGroupId' => 'CG020', 'maxCapacity' => 30,  'currentEnrollment' => 20],
        ];

        // Add timestamps to all records
        foreach ($groups as &$group) {
            $group['created_at'] = $now;
            $group['updated_at'] = $now;
        }

        DB::table('classGroup')->insert($groups);
    }
}