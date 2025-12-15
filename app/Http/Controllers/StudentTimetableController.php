<?php

namespace App\Http\Controllers;

use App\Mediator\TimetableMediatorInterface; 
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StudentTimetableController extends Controller
{
    protected $mediator;

    public function __construct(TimetableMediatorInterface $mediator)
    {
        // Save the injected object into the class property
        $this->mediator = $mediator;
    }


    // -------------------- For Timetable Page --------------------
    public function index(Request $request)
    {
        // Get user id
        $user = Auth::user();
        $userId = $user->userId;

        // Get the weeks for dropdown (from start of semester, monday)
        $weeks = [];
        $semesterSession = Carbon::create(2025, 11, 3);
        for($i = 1; $i < 8; $i++) {
            // Short sem
            $week = $semesterSession->addWeek();
            $label = "Week " . $i;

            // Populate the list
            $weeks[$week->format('Y-m-d')] = $label;
        }


        // get the date from URL (if any)
        $selectedDate = $request->input('date') ? $request->input('date') : "";


        // Get all the classes the user has (in the selected week)
        $weeklyClasses = $this->mediator->getCurrentWeekSchedule($userId, $selectedDate);


        // check if public holiday, then type the timeslot (hardcoded)
        $holidays = [
            '2025-12-25' => 'Christmas Day',
            '2025-01-01' => 'New Year',
        ];


        // TH for UI
        $timeSlots = [
            "08:00",
            "09:00",
            "10:00",
            "11:00",
            "12:00",
            "13:00",
            "14:00",
            "15:00",
            "16:00",
            "17:00",
            "18:00",
            "19:00",
            "20:00",
            "21:00",
            "22:00",
        ];

        // Column 1 for UI
        $days = [];
        // Make into easily manipulated Laravel Carbon date object
        $movingWeek = Carbon::parse($selectedDate); 

        for($i = 0; $i < 7; $i++) {
            if($i != 0)
                $movingWeek->addDay();

            // $movingWeek is a manipulatable Laravel Carbon date object
            $days[] = ['name' => $movingWeek->format('D'), 'date' => $movingWeek->format('Y-m-d')];

        }

        
        // Check if Replacement Reminder is needed
        $timetableData = [];
        $hasReplacement = false; 
        $replacementClassList = [];

        foreach($weeklyClasses as $class) {

            // Check if there is replacement classes
            if ($class->classMode === 'replacement') {
                $hasReplacement = true;
                $replacementClassList[] = $class;
            }

            // Check for holiday
            $type = "normal";
            $note = null;

            // If there is a holiday, what holiday is it?
            if(array_key_exists($class->classDate, $holidays)){
                $type = "canceled";
                $event = $holidays[$class->classDate];
            }

            // After filtering normal/replacement/canceled, add the class to the list
            $day = substr($class->dayOfWeek, 0, 3); 
            $startTime = Carbon::parse($class->startTime);
            $endTime = Carbon::parse($class->endTime);
            $duration = $startTime->diffInHours($endTime);

            $transitiveSubjectList = $this->mediator->getTransitiveSubjectList();


            // [Mon][08:00]
            $timetableData[$day][$startTime->format('H:i')] = [
                'timetableId' => $class->timetableId,
                'subject' => $transitiveSubjectList[$class->courseSubjectId],
                'venueId' => $class->venueId,
                'duration' => $duration,
                'type' => $type,
                'time_str' => $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A'),
                'note' => $note
            ];
        }

        //dd($timetableData);

        // weeks for dropdownList
        // timeSlots, days for UI
        // timetableData, for table body
        // hasReplacement for pop-up 
        // replacementClassList for pop-up info
        return view('student.timetableModule.timetable', compact('weeks', 'timeSlots', 'days', 'timetableData', 'hasReplacement', 'replacementClassList'));
    }


    public function viewOptions()
    {
        // 1. Hardcoded User Info (Simulating User Management Module)
        // In the future, this will come from Auth::user()->course or similar.
        $userInfo = [
            'course' => 'RSD',
            'group'  => 'G4'
        ];

        // 2. Hardcoded Replacement Options Data
        // Grouped by Day to match your UI design
        $groupedOptions = [
            'Monday' => [
                [
                    'id' => 301,
                    'subject' => 'BMIT3173(L)',
                    'time' => '8:00a.m. - 10:00a.m.',
                    'lecturer' => 'Malarvili',
                    'venue' => 'DK4'
                ],
                [
                    'id' => 302,
                    'subject' => 'BMIT3173(L)',
                    'time' => '8:00a.m. - 10:00a.m.',
                    'lecturer' => 'Malarvili',
                    'venue' => 'DK4'
                ]
            ],
            'Wednesday' => [
                [
                    'id' => 303,
                    'subject' => 'BMIT3173(L)',
                    'time' => '8:00a.m. - 10:00a.m.',
                    'lecturer' => 'Malarvili',
                    'venue' => 'DK4'
                ],
                [
                    'id' => 304,
                    'subject' => 'BMIT3173(L)',
                    'time' => '8:00a.m. - 10:00a.m.',
                    'lecturer' => 'Malarvili',
                    'venue' => 'DK4'
                ]
            ]
        ];

        return view('student.timetableModule.replacement_options', compact('userInfo', 'groupedOptions'));
    }
    

    public function selectTimeslot(Request $request)
    {
        // 1. Get Parameters (passed from previous page)
        // In a real app, you'd use $request->input('subject_code') etc.
        $selectedSubject = "BMIT3173(L)";
        $selectedDay = "Monday";

        // 2. Logic: Mocking the "Student's Existing Timetable"
        // These will be displayed as GREYED OUT to show the user their busy slots.
        $existingSchedule = [
            'Mon' => [
                '08:00' => ['duration' => 2, 'subject' => 'BMIT3173(L)', 'venue' => 'DK 4', 'type' => 'existing'],
            ],
            'Wed' => [
                '10:00' => ['duration' => 2, 'subject' => 'BJEL2013(T)', 'venue' => 'K303', 'type' => 'existing'],
                '13:00' => ['duration' => 2, 'subject' => 'BJEL2013(T)', 'venue' => 'Q207A', 'type' => 'existing'],
            ],
            // ... add other existing classes if needed
        ];

        // 3. Logic: Mocking the "Filtered Valid Replacement Options"
        // These are the specific slots the Algorithm found for BMIT3173(L).
        // These will be GREEN and CLICKABLE.
        $replacementOptions = [
            'Thu' => [
                '10:00' => [
                    'id' => 501, 
                    'duration' => 2, 
                    'subject' => 'BMIT3173(L)', 
                    'venue' => 'DK 4', 
                    'lecturer' => 'Malarvili',
                    'type' => 'candidate' // Special type for this page
                ]
            ],
            'Sat' => [
                '12:00' => [
                    'id' => 502, 
                    'duration' => 2, 
                    'subject' => 'BMIT3173(P)', 
                    'venue' => 'D203F', 
                    'lecturer' => 'Ng Yi Ling', 
                    'type' => 'candidate'
                ]
            ]
        ];

        // 4. Merge Data for the View
        // We combine them into one $scheduleData array. 
        // If a collision happens, usually the 'candidate' logic would handle it, 
        // but here we assume the algorithm already filtered out clashes.
        $scheduleData = array_merge_recursive($existingSchedule, $replacementOptions);

        // 5. Standard Grid Data
        $timeSlots = [];
        for ($hour = 8; $hour <= 22; $hour++) {
            $timeSlots[] = sprintf("%02d:00", $hour);
        }
        
        $days = [
            ['name' => 'Mon', 'date' => '2025-12-22'],
            ['name' => 'Tue', 'date' => '2025-12-23'],
            ['name' => 'Wed', 'date' => '2025-12-24'],
            ['name' => 'Thu', 'date' => '2025-12-25'],
            ['name' => 'Fri', 'date' => '2025-12-26'],
            ['name' => 'Sat', 'date' => '2025-12-27'],
            ['name' => 'Sun', 'date' => '2025-12-28'],
        ];

        return view('student.timetableModule.select_timeslot', compact('scheduleData', 'timeSlots', 'days', 'selectedSubject', 'selectedDay'));
    }

}