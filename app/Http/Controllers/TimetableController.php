<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class TimetableController extends Controller
{
    public function index()
    {
        // 1. Hardcoded Weeks for the Dropdown
        $weeks = [
            '2025-12-15' => 'Week 1',
            '2025-12-22' => 'Week 2 (Current)',
            '2025-12-29' => 'Week 3',
        ];

        // 2. Logic: Check if user has upcoming replacement (for the Red Reminder)
        $hasReplacement = true; 

        // 3. Hardcoded Timetable Data
        // In a real app, this comes from the Mediator/Database
        $scheduleData = [
            'Mon' => [
                '08:00' => [
                    'id' => 101,
                    'subject' => 'BMIT3173 (L)',
                    'venue' => 'DK 4',
                    'lecturer' => 'Malarvili',
                    'duration' => 2, // Duration in hours (controls colspan)
                    'type' => 'normal', // normal, replacement, clash
                    'time_str' => '8:00 AM - 10:00 AM'
                ],
            ],
            'Wed' => [
                '10:00' => [
                    'id' => 102,
                    'subject' => 'BJEL2013 (T)',
                    'venue' => 'K303',
                    'lecturer' => 'Christopher Quah',
                    'duration' => 2,
                    'type' => 'normal',
                    'time_str' => '10:00 AM - 12:00 PM'
                ],
                '13:00' => [
                    'id' => 103,
                    'subject' => 'BJEL2013 (T)',
                    'venue' => 'Q207A',
                    'lecturer' => 'Christopher Quah',
                    'duration' => 2,
                    'type' => 'replacement', // User cannot click this
                    'note' => '(Replaced class for 2025-12-25)',
                    'time_str' => '1:00 PM - 3:00 PM'
                ]
            ],
            'Thu' => [
                '10:00' => [
                    'id' => 999,
                    'subject' => 'BMIT3173 (L)',
                    'venue' => 'DK 4',
                    'lecturer' => 'Malarvili',
                    'duration' => 2,
                    'type' => 'clash', // User cannot click this
                    'note' => 'Christmas',
                    'time_str' => '10:00 AM - 12:00 PM'
                ]
            ],
            'Sat' => [
                '12:00' => [
                    'id' => 104,
                    'subject' => 'BMIT3173 (P)',
                    'venue' => 'D203F',
                    'lecturer' => 'Ng Yi Ling',
                    'duration' => 2,
                    'type' => 'normal',
                    'time_str' => '12:00 PM - 2:00 PM'
                ],
                '17:00' => [
                    'id' => 105,
                    'subject' => 'BMIT3173 (P)',
                    'venue' => 'B001E',
                    'lecturer' => 'Ng Yi Ling',
                    'duration' => 2,
                    'type' => 'normal',
                    'time_str' => '5:00 PM - 7:00 PM'
                ]
            ]
        ];

        // 4. Time Slots (Columns) - 08:00 to 22:00
        $timeSlots = [];
        for ($hour = 8; $hour <= 22; $hour++) {
            $timeSlots[] = sprintf("%02d:00", $hour);
        }

        // 5. Days (Rows) with dates hardcoded for Week 2
        $days = [
            ['name' => 'Mon', 'date' => '2025-12-22'],
            ['name' => 'Tue', 'date' => '2025-12-23'],
            ['name' => 'Wed', 'date' => '2025-12-24'],
            ['name' => 'Thu', 'date' => '2025-12-25'],
            ['name' => 'Fri', 'date' => '2025-12-26'],
            ['name' => 'Sat', 'date' => '2025-12-27'],
            ['name' => 'Sun', 'date' => '2025-12-28'],
        ];

        return view('student.timetable', compact('weeks', 'hasReplacement', 'scheduleData', 'timeSlots', 'days'));
    }
}