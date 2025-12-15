<?php

namespace App\Mediator;
use Carbon\Carbon;
use App\Models\Timetable;
use App\Models\Venue;
use App\Models\ClassGroup;
use App\Models\CourseSubject;
use App\Services\CourseService;
use App\Services\CourseSubjectService;
use App\Services\SubjectService;
use App\Services\UserService;
use Dflydev\DotAccessData\Data;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;

class TimetableMediator implements TimetableMediatorInterface
{
    // Assume the Course Service API is stored here
    protected $courseApi;
    protected $subjectApi;
    protected $courseSubjectApi;
    protected $userApi;

    public function __construct(CourseService $courseApi, SubjectService $subjectApi, CourseSubjectService $courseSubjectApi, UserService $userApi)
    {
        $this->courseApi = $courseApi;
        $this->subjectApi = $subjectApi;
        $this->courseSubjectApi = $courseSubjectApi;
        $this->userApi = $userApi;
    }

    // =========================== FOR STUDENTS TIMETABLE PAGE ==============================
    public function getCurrentWeekSchedule($studentId, $selectedDate) {
        $selectedDate = Carbon::parse($selectedDate);
        $startOfWeek = $selectedDate;
        $endOfWeek = $selectedDate->copy()->addDays(6);

        // Get user details
        $studentProfile = $this->userApi->getUser($studentId);

        // To get their classGroup
        if (!$studentProfile || empty($studentProfile['classGroupId'])) {
            return collect([]);
        }

        // Fetch
        $classGroupId = $studentProfile['classGroupId'];
        $startOfWeek = Carbon::parse($selectedDate)->startOfWeek()->format('Y-m-d');
        $endOfWeek = Carbon::parse($selectedDate)->endOfWeek()->format('Y-m-d');

        $classes = Timetable::with(['venue', 'courseSubject'])
            // get both solo class and joint class
            ->where(function ($query) use ($classGroupId) {
                                $query->where('classGroupId', $classGroupId)
                      ->orWhereRaw("FIND_IN_SET(?, classGroupId) > 0", [$classGroupId]);
            })
            ->whereBetween('classDate', [$startOfWeek, $endOfWeek])
            ->orderBy('classDate')
            ->orderBy('startTime')
            ->get();        


        return $classes;
    }

    public function getTransitiveSubjectList() {
        // subjectId, subjectName
        //$subjectRecords = $this->courseApi->fetchSubjectsFromApi(); // !!!!! FLAWED !!!!!
        $subjectRecords = DB::table('subject')->get()->toArray();

        //dd($subjectRecords);

        // subjectId -> subjectName
        $subjectMap = [];
        if (is_array($subjectRecords)) {
            foreach ($subjectRecords as $subject) {
                //$subjectMap[$subject['subjectId']] = $subject['subjectName'];
                $subjectMap[$subject->subjectId] = $subject->subjectName;
            }
        }

        // courseSubjectId, courseId, subjectId
        //$courseSubjectRecords = $this->courseSubjectApi->getAllCourseSubjects(); // !!!!! FLAWED !!!!!!
        $courseSubjectRecords = DB::table('course_subject')->get();

        //dd($courseSubjectRecords);

        // result is: courseSubjectId -> subjectName
        $transitiveMap = [];


        foreach ($courseSubjectRecords as $courseSubject) {

            // S0001
            $key = $courseSubject->subjectId;

            // (CourseSubject) S0001 == S001 (Subject)
            if (array_key_exists($key, $subjectMap)) {
                // CS0001 -> subjectName
                $transitiveMap[$courseSubject->courseSubjectId] = $subjectMap[$key];
            } else {
                $transitiveMap[$courseSubject->courseSubjectId] = $key;
            }
        }

        //dd($transitiveMap);

        return $transitiveMap;
    }
    

    // ================================ For Admin Timetable Assignment ===========================================

    public function getAllCourses() {
        $rawCourses = $this->courseApi->fetchCoursesFromApi();
        $result = [];
        
        foreach($rawCourses as $course) {
            $result[$course['courseId']] = $course['courseCode'];
        }
        
        return $result;
    }


    public function getAllSubjects()
    {
        $subjects = $this->subjectApi->fetchSubjectsFromApi();

        $subjectsData = [];
        
        foreach ($subjects as $subject) {
            $subjectsData[] = [
                'subjectId' => $subject['subjectId'], 
                'subjectCode' => $subject['subjectCode'],
                'subjectName' => $subject['subjectName'],
            ];
        }

        return $subjectsData;
    }


    public function getAvailableTimeslots($day, $venueId, $duration) {
        
        // Hardcoded hours
        $hours = [
            '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', 
            '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', 
            '20:00', '21:00'
        ];

        $availableSlots = [];
        
        // Occupied timeslots
        $occupiedSlots = Timetable::where('dayOfWeek', $day)->where('venueId', $venueId)->select('startTime', 'endTime')->get();

        // Check if timeslot is clashing
        foreach ($hours as $hour) {
            $start = Carbon::createFromFormat('H:i', $hour);
            $end = $start->copy()->addHours($duration);

            $isFree = true;

            // Check against every busy slot
            foreach ($occupiedSlots as $occupiedSlot) {
                $occupiedStart = Carbon::parse($occupiedSlot->startTime);
                $occupiedEnd = Carbon::parse($occupiedSlot->endTime);

                // Clash check
                // start < occupiedEndTime = there is class at the second half of timeslot
                // end > occupiedStartTime = there is class at first hald of timeslot
                if ($start->lessThan($occupiedEnd) && $end->greaterThan($occupiedStart)) {
                    $isFree = false;
                    break;
                }
            }

            // Format
            $availableSlots[] = [
                'label' => $start->format('g:i A') . ' - ' . $end->format('g:i A'),
                'start' => $start->format('H:i:s'),
                'available' => $isFree,
            ];
        }

        return $availableSlots;
    }

    public function assignClass($data) {
        // --- 1. PREPARE DATA ---

        // Day String Fix (Accepts Sat, Mon, Saturday, etc. and ensures it's the full name for Carbon/DB)
        $dayString = ucfirst(strtolower($data['day']));
        $dayMap = ['Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday'];
        $fullDayName = $dayMap[substr($dayString, 0, 3)] ?? $dayString;

        $startTime = Carbon::createFromFormat('H:i', $data['selectedTimeslot']);
        $duration = 2; // Fixed to 2 hours
        $endTime = $startTime->copy()->addHours($duration)->format('H:i:s');
        $startTimeFormatted = $startTime->format('H:i:s');

        // Group ID String (e.g., "CG001,CG002")
        $classGroupIds = is_array($data['classGroupIds']) ? implode(',', $data['classGroupIds']) : $data['classGroupIds'];
        
        // Find the correct courseSubjectId (The Bridge)
        $courseSubject = \App\Models\CourseSubject::where('subjectId', $data['subjectId']) // CALL FROM API
            ->where('courseId', $data['courseId'])
            ->first();
        
        if (!$courseSubject) {
            Log::error("Timetable Creation Failed: CourseSubject link missing for Subject {$data['subjectId']} and Course {$data['courseId']}");
            return 'course_subject_link_missing'; 
        }
        $courseSubjectId = $courseSubject->courseSubjectId; 

        // Determine the start date: The first instance of that day in the future
        $currentDate = Carbon::now()->next($fullDayName);
        $totalWeeks = 7; // Business rule: Create 7 records

        // --- 2. CLASH CHECK (The Business Rule) ---
        // Check ONLY the first occurrence against existing records for the same day/venue/time
        $isOccupied = Timetable::where('dayOfWeek', $fullDayName) 
            ->where('venueId', $data['venueId'])
            ->where('startTime', '<', $endTime)
            ->where('endTime', '>', $startTimeFormatted)
            ->exists();

        if ($isOccupied) {
            return 'clash'; // Fail fast if the slot is busy in ANY week
        }

        // --- 3. CREATE LOGIC (LOOP) ---
        try {
            $logData = [];
            
            for ($i = 0; $i < $totalWeeks; $i++) {
                
                $timetableData = [
                    // Generate a unique ID for EACH record
                    'timetableId' => $this->generateNextTimetableId(), 
                    'classGroupId' => $classGroupIds,
                    'courseSubjectId' => $courseSubjectId,
                    'userId' => $data['lecturerId'],
                    'venueId' => $data['venueId'],
                    'dayOfWeek' => $fullDayName,
                    'startTime' => $startTimeFormatted,
                    'endTime' => $endTime,
                    'classDate' => $currentDate->format('Y-m-d'), // Use the specific date
                    'classType' => $data['classType'],
                    'classMode' => 'weekly',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                // Create the record
                Timetable::create($timetableData);
                
                $logData[] = $timetableData['timetableId'] . " on " . $timetableData['classDate'];

                // Move to the next week for the next loop iteration
                $currentDate->addWeek();
            }
            
            Log::info('Timetable created successfully for ' . $totalWeeks . ' weeks.', ['records' => $logData]);
            return true;
            
        } catch (\Exception $e) {
            // Log the actual error for debugging
            Log::error('Timetable Creation Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // Re-throw or return 'error'
            return 'error';
        }
    }

    protected function generateNextTimetableId() {
        // 1. Get the latest ID
        $latest = Timetable::latest('timetableId')->first();
        
        if (!$latest) {
            return 'T001'; // Start at T001 if the table is empty
        }

        // 2. Extract the number, increment it, and pad it
        $lastId = $latest->timetableId; // e.g., T150
        $number = (int) substr($lastId, 1); // Get 150
        $newNumber = $number + 1; // Get 151
        
        // Pad the number back to 3 digits (e.g., 151 -> T151, 9 -> T009)
        return 'T' . str_pad($newNumber, 3, '0', STR_PAD_LEFT); 
        // NOTE: If you have IDs T001 to T999, T1000 will be T1000, 
        // so change padding from 3 to 4 if needed.
    }

}
