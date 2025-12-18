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
        for ($i = 1; $i < 8; $i++) {
            // Short sem
            $week = $semesterSession->addWeek();
            $label = "Week " . $i;

            // Populate the list
            $weeks[$week->format('Y-m-d')] = $label;
        }


        // get the date from URL (if any)
        $selectedDate = $request->input('date') ? $request->input('date') : "2025-11-10";


        // Get all the classes the user has (in the selected week)
        $weeklyClasses = $this->mediator->getCurrentWeekSchedule($userId, $selectedDate);

        //dd($weeklyClasses);

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

        for ($i = 0; $i < 7; $i++) {
            if ($i != 0)
                $movingWeek->addDay();

            // $movingWeek is a manipulatable Laravel Carbon date object
            $days[] = ['name' => $movingWeek->format('D'), 'date' => $movingWeek->format('Y-m-d')];
        }


        // Check if Replacement Reminder is needed
        $timetableData = [];
        $hasReplacement = false;
        $replacementClassList = [];

        foreach ($weeklyClasses as $class) {
            // Check if there is replacement classes
            if ($class['classType'] === 'replacement') {
                $hasReplacement = true;
                $replacementClassList[] = $class;
            }

            // Check for holiday
            $type = "normal";
            $note = null;

            // If there is a holiday, what holiday is it?
            if (array_key_exists($class['classDate'], $holidays)) {
                $type = "canceled";
                $event = $holidays[$class['classDate']];
            }

            // After filtering normal/replacement/canceled, add the class to the list
            $day = substr($class['dayOfWeek'], 0, 3);
            $startTime = Carbon::parse($class['startTime']);
            $endTime = Carbon::parse($class['endTime']);
            $duration = $startTime->diffInHours($endTime);

            $transitiveSubjectList = $this->mediator->getTransitiveSubjectList();

            // [Mon][08:00]
            $timetableData[$day][$startTime->format('H:i')] = [
                'timetableId' => $class['timetableId'],
                'subject' => $transitiveSubjectList[$class['courseSubjectId']],
                'venueId' => $class['venueId'],
                'duration' => $duration,
                'type' => $type,
                'time_str' => $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A'),
                'note' => $note
            ];
        }

        return view('student.timetableModule.timetable', compact('weeks', 'timeSlots', 'days', 'timetableData', 'hasReplacement', 'replacementClassList'));
    }

    // --------------------------------------------------------------------------------------------
    // ----------------------------------- SELECT TIMESLOT PAGE -----------------------------------
    // --------------------------------------------------------------------------------------------
    public function viewSelectTimeslot(Request $request)
{
    $userId = Auth::user()->userId;
    $selectedClass = session('replacement_selected_class');

    if (!$selectedClass) {
        return redirect()->route('replacement.appeal')->with('error', 'No class was selected for replacement.');
    }

    $originalClassId = $selectedClass['timetableId'];
    $validSlots = $this->mediator->getValidReplacementOptions($userId, $originalClassId);

    // Debug the valid slots times
    $allTimes = [];
    if ($validSlots && is_iterable($validSlots)) {
        foreach ($validSlots as $slot) {
            if (isset($slot['startTime'])) {
                $time = Carbon::parse($slot['startTime'])->format('H:i');
                $allTimes[] = $time;
            }
        }
    }
    
    // Create time slots based on actual data + standard slots
    $uniqueTimes = array_unique($allTimes);
    sort($uniqueTimes);
    $timeSlots = $uniqueTimes ?: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];
    
    // If we have standard 2-hour slots, add them for better grid
    if (!in_array('09:00', $timeSlots) && !in_array('11:00', $timeSlots)) {
        $timeSlots = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];
    }

    // Start from the original class date's week
    $originalDate = Carbon::parse($selectedClass['classDate']);
    $startDate = $originalDate->copy()->startOfWeek();
    
    $days = [];
    for ($i = 0; $i < 7; $i++) {
        $date = $startDate->copy()->addDays($i);
        $days[] = [
            'name' => $date->format('D'),
            'date' => $date->format('Y-m-d')
        ];
    }

    $originalClass = (object) $selectedClass;
    $selectedSubject = $originalClass->subjectName ?? null;
    $selectedDay = $originalDate->format('l');

    $scheduleData = [];
    // Initialize empty schedule for the week
    for ($i = 0; $i < 7; $i++) {
        $date = $startDate->copy()->addDays($i);
        $dayName = $date->format('D');
        $shortDayName = substr($dayName, 0, 3);

        foreach ($timeSlots as $time) {
            $scheduleData[$shortDayName][$time] = null;
        }
    }

    // Mark the original class
    $originalDay = $originalDate->format('D');
    $originalShortDay = substr($originalDay, 0, 3);
    $originalTime = Carbon::parse($selectedClass['startTime'])->format('H:i');

    // Set original class - FORCE it to be set
    if (isset($scheduleData[$originalShortDay])) {
        // Find the closest time slot
        $closestTime = null;
        $minDiff = PHP_INT_MAX;
        
        foreach (array_keys($scheduleData[$originalShortDay]) as $slotTime) {
            $diff = abs(strtotime($slotTime) - strtotime($originalTime));
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closestTime = $slotTime;
            }
        }
        
        if ($closestTime) {
            $scheduleData[$originalShortDay][$closestTime] = [
                'subject' => $originalClass->subjectName,
                'venue' => $originalClass->venueId ?? 'N/A',
                'duration' => 1,
                'type' => 'existing',
                'id' => $originalClass->timetableId
            ];
        }
    }

    // Mark valid replacement slots as candidates
    if ($validSlots && is_iterable($validSlots)) {
        foreach ($validSlots as $slot) {
            if (isset($slot['classDate']) && isset($slot['startTime'])) {
                $slotDate = Carbon::parse($slot['classDate']);
                $slotDay = $slotDate->format('D');
                $shortSlotDay = substr($slotDay, 0, 3);
                $slotTime = Carbon::parse($slot['startTime'])->format('H:i');
                
                if (isset($scheduleData[$shortSlotDay])) {
                    // Find closest time slot
                    $closestTime = null;
                    $minDiff = PHP_INT_MAX;
                    
                    foreach (array_keys($scheduleData[$shortSlotDay]) as $gridTime) {
                        $diff = abs(strtotime($gridTime) - strtotime($slotTime));
                        if ($diff < $minDiff) {
                            $minDiff = $diff;
                            $closestTime = $gridTime;
                        }
                    }
                    
                    if ($closestTime && $scheduleData[$shortSlotDay][$closestTime] === null) {
                        $scheduleData[$shortSlotDay][$closestTime] = [
                            'subject' => $slot['subjectCode'] ?? $originalClass->subjectName,
                            'venue' => $slot['venueId'] ?? 'N/A',
                            'duration' => 1,
                            'type' => 'candidate',
                            'id' => $slot['timetableId'] ?? null,
                            'lecturer' => $slot['lecturerName'] ?? null
                        ];
                    }
                }
            }
        }
    }

    // Final debug
    $filledSlots = 0;
    foreach ($scheduleData as $day => $times) {
        foreach ($times as $time => $data) {
            if ($data !== null) $filledSlots++;
        }
    }


    return view('student.timetableModule.select_timeslot', compact(
        'validSlots',   
        'timeSlots',
        'days',
        'originalClass',
        'selectedSubject',
        'selectedDay',
        'scheduleData'
    ));
}

    // POST: Store selected class data in session
    public function storeSelectedClass(Request $request)
    {
        session([
            'replacement_selected_class' => [
                'timetableId' => $request->timetableId,
                'courseSubjectId' => $request->courseSubjectId,
                'classType' => $request->classType,
                'classDate' => $request->classDate,
                'startTime' => $request->startTime,
                'endTime' => $request->endTime,
                'venueId' => $request->venueId,
                'classGroupId' => $request->classGroupId,
                'subjectCode' => $request->subjectCode,
                'subjectName' => $request->subjectName,
                'selected_at' => now()->toDateTimeString()
            ]
        ]);

        // Go back
        return redirect()->route('student.selectTimeslot');
    }
}
