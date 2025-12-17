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

        // Get data from session instead of request parameter
        $selectedClass = session('replacement_selected_class');

        if (!$selectedClass) {
            return redirect()->route('replacement.appeal')->with('error', 'No class was selected for replacement.');
        }

        $originalClassId = $selectedClass['timetableId'];

        $validSlots = $this->mediator->getValidReplacementOptions($userId, $originalClassId);

        // For UI
        $timeSlots = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];
        $days = [];
        $startDate = Carbon::tomorrow();
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $days[] = [
                'name' => $date->format('D'), // 'Mon', 'Tue', etc
                'date' => $date->format('Y-m-d') // Full date
            ];
        }
        $originalClass = (object) $selectedClass; // Convert to object for easy access in view

        // Fix: Add the missing variables that the view expects
        $selectedSubject = $originalClass->subjectName ?? 'Unknown Subject';
        $selectedDay = date('l', strtotime($originalClass->classDate)) ?? 'Unknown Day';

        // Fix: Create scheduleData structure for the timetable grid
        // Transform $validSlots into the $scheduleData format
        $scheduleData = [];

        // Get next 7 days starting from tomorrow
        $startDate = Carbon::tomorrow();
        $daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        // Initialize empty schedule for next 7 days
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dayName = $date->format('D');
            $shortDayName = substr($dayName, 0, 3); // Get "Mon", "Tue", etc

            // Initialize time slots for this day
            foreach ($timeSlots as $time) {
                $scheduleData[$shortDayName][$time] = null;
            }
        }

        // Mark existing/occupied slots (you might need to get these from your mediator)
        // For now, we'll mark the original class slot as existing
        $originalDay = Carbon::parse($originalClass->classDate)->format('D');
        $originalShortDay = substr($originalDay, 0, 3);
        $originalTime = date('H:i', strtotime($originalClass->startTime));

        if (isset($scheduleData[$originalShortDay][$originalTime])) {
            $scheduleData[$originalShortDay][$originalTime] = [
                'subject' => $originalClass->subjectName,
                'venue' => $originalClass->venueId ?? 'N/A',
                'duration' => 1,
                'type' => 'existing',
                'id' => $originalClass->timetableId
            ];
        }

        // Mark valid replacement slots as candidates
        if ($validSlots && is_iterable($validSlots)) {
            foreach ($validSlots as $slot) {
                if (isset($slot['classDate']) && isset($slot['startTime'])) {
                    $slotDay = Carbon::parse($slot['classDate'])->format('D');
                    $shortSlotDay = substr($slotDay, 0, 3);
                    $slotTime = date('H:i', strtotime($slot['startTime']));

                    // Find matching time slot (closest match)
                    $closestTime = null;
                    foreach ($timeSlots as $timeSlot) {
                        if (abs(strtotime($timeSlot) - strtotime($slotTime)) < 3600) { // Within 1 hour
                            $closestTime = $timeSlot;
                            break;
                        }
                    }

                    if ($closestTime && isset($scheduleData[$shortSlotDay][$closestTime])) {
                        $scheduleData[$shortSlotDay][$closestTime] = [
                            'subject' => $slot['subjectName'] ?? $originalClass->subjectName,
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

        return view('student.timetableModule.select_timeslot', compact(
            'validSlots',
            'timeSlots',
            'days',
            'originalClass',
            'selectedSubject',    // Add this
            'selectedDay',        // Add this
            'scheduleData'        // Add this (now populated)
        ));
    }

    // Store selected class data in session
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
