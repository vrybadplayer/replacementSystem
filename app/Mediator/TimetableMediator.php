<?php

namespace App\Mediator;

use Carbon\Carbon;
use App\Models\Timetable;
use App\Services\ClassGroupService;
use App\Services\CourseSubjectManagementService;
use App\Services\TimetableService;
use App\Services\UserService;
use App\Services\VenueService;
use App\Services\ReplacementService;
use Illuminate\Support\Facades\Log;

class TimetableMediator implements TimetableMediatorInterface
{

    protected $courseSubjectmanagementApi;
    protected $userApi;
    protected $timetableApi;
    protected $venueApi;
    protected $classGroupApi;
    protected $replacementApi;

    public function __construct(
        CourseSubjectManagementService $courseSubjectmanagementApi,
        UserService $userApi,
        TimetableService $timetableApi,
        VenueService $venueApi,
        ClassGroupService $classGroupApi,
        ReplacementService $replacementApi
    ) {
        $this->courseSubjectmanagementApi = $courseSubjectmanagementApi;
        $this->userApi = $userApi;
        $this->timetableApi = $timetableApi;
        $this->venueApi = $venueApi;
        $this->classGroupApi = $classGroupApi;
        $this->replacementApi = $replacementApi;
    }

    // ======================================================================================
    // =========================== FOR STUDENTS TIMETABLE PAGE ==============================
    // ======================================================================================
    public function getCurrentWeekSchedule($studentId, $selectedDate)
    {
        $date = Carbon::parse($selectedDate);

        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        // Get user details
        $studentProfile = $this->userApi->getUser($studentId);

        if (!$studentProfile || empty($studentProfile['classGroupId'])) {
            return collect([]);
        }

        $classGroupId = $studentProfile['classGroupId'];

        $classes = Timetable::with(['venue', 'courseSubject'])
            ->where(function ($query) use ($classGroupId) {
                $query->where('classGroupId', $classGroupId)
                    ->orWhereRaw("FIND_IN_SET(?, classGroupId) > 0", [$classGroupId]);
            })
            ->whereBetween('classDate', [$startOfWeek, $endOfWeek])
            ->orderBy('classDate')
            ->orderBy('startTime')
            ->get()
            ->toArray();

        return $classes;
    }

    public function getTransitiveSubjectList()
    {
        // subjectId, subjectName
        $subjectRecords = $this->courseSubjectmanagementApi->fetchSubjectsFromApi();

        // subjectId -> subjectName
        $subjectMap = [];
        if (is_array($subjectRecords)) {
            foreach ($subjectRecords as $subject) {
                $subjectMap[$subject['subjectId']] = $subject['subjectName'];
            }
        }

        // courseSubjectId, courseId, subjectId
        $courseSubjectRecords = $this->courseSubjectmanagementApi->fetchCourseSubjectsFromApi();

        // desired result: courseSubjectId -> subjectName
        $transitiveMap = [];


        foreach ($courseSubjectRecords as $courseSubject) {

            // S0001
            $key = $courseSubject['subjectId'];

            // (CourseSubject) S0001 == S001 (Subject)
            if (array_key_exists($key, $subjectMap)) {
                // CS0001 -> subjectName
                $transitiveMap[$courseSubject['courseSubjectId']] = $subjectMap[$key];
            } else {
                $transitiveMap[$courseSubject['courseSubjectId']] = $key;
            }
        }

        return $transitiveMap;
    }


    // ===========================================================================================================
    // ================================ For Admin Timetable Assignment ===========================================
    // ===========================================================================================================

    public function getAllCourses()
    {
        $rawCourses = $this->courseSubjectmanagementApi->fetchCoursesFromApi();
        $result = [];

        foreach ($rawCourses as $course) {
            $result[$course['courseId']] = $course['courseCode'];
        }

        return $result;
    }


    public function getAllSubjects()
    {
        $subjects = $this->courseSubjectmanagementApi->fetchSubjectsFromApi();

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

    public function getAvailableTimeslots($day, $venueId, $duration)
    {

        // Hardcoded hours
        $hours = [
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00',
            '18:00',
            '19:00',
            '20:00',
            '21:00'
        ];

        $availableSlots = [];

        // Occupied timeslots
        $occupiedSlots = $this->timetableApi->getOccupiedSlots($day, $venueId);

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

    public function assignClass($data)
    {

        // Data Formatting
        $dayString = ucfirst(strtolower($data['day'])); // mOnDay => monday => Monday
        $dayMap = ['Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday'];
        $fullDayName = $dayMap[substr($dayString, 0, 3)] ?? $dayString;

        $duration = 2;
        $startTime = Carbon::createFromFormat('H:i', $data['selectedTimeslot']);
        $endTime = $startTime->copy()->addHours($duration)->format('H:i:s');
        $startTimeFormatted = $startTime->format('H:i:s');

        // If more than 1 ClassGroup, concat
        $classGroupIds = is_array($data['classGroupIds']) ? implode(',', $data['classGroupIds']) : $data['classGroupIds'];

        // Get CourseSubject
        $courseSubject = $this->courseSubjectmanagementApi->fetchCourseSubjectWithForeignKeys($data['subjectId'], $data['courseId']);

        if (!$courseSubject) {
            Log::error("Timetable Creation Failed: CourseSubject link missing for Subject {$data['subjectId']} and Course {$data['courseId']}");
            return 'course_subject_link_missing';
        }

        $courseSubjectId = $courseSubject['courseSubjectId'];
        $currentDate = Carbon::now()->next($fullDayName);
        $totalWeeks = 7;

        // Clash Checking
        $isOccupied = $this->timetableApi->clashCheck(
            $fullDayName,
            $data['venueId'],
            $startTimeFormatted,
            $endTime
        );

        if ($isOccupied) {
            return 'clash';
        }

        // Insert 7 records
        try {
            $logData = [];

            for ($i = 0; $i < $totalWeeks; $i++) {

                $timetableData = [
                    'timetableId' => $this->generateNextTimetableId(),
                    'classGroupId' => $classGroupIds,
                    'courseSubjectId' => $courseSubjectId,
                    'userId' => $data['lecturerId'],
                    'venueId' => $data['venueId'],
                    'dayOfWeek' => $fullDayName,
                    'startTime' => $startTimeFormatted,
                    'endTime' => $endTime,
                    'classDate' => $currentDate->format('Y-m-d'),
                    'classType' => $data['classType'],
                    'classMode' => 'weekly',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                // Create the record
                $this->timetableApi->createTimetable($timetableData);

                $logData[] = $timetableData['timetableId'] . " on " . $timetableData['classDate'];
                $currentDate->addWeek();
            }

            return true;
        } catch (\Exception $e) {
            return 'error';
        }
    }

    protected function generateNextTimetableId()
    {
        // Get latest ID
        $latest = Timetable::latest('timetableId')->first();

        $lastId = $latest->timetableId;

        // Cast + Get number
        $number = (int) substr($lastId, 1);

        $newNumber = $number + 1;

        return 'T' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    // ======================================================================================================
    // ==================================== Timeslot Management =============================================
    // ======================================================================================================

    public function getAllCourseCodes()
    {
        $rawCourses = $this->courseSubjectmanagementApi->fetchCoursesFromApi();
        $courses = [];

        foreach ($rawCourses as $course) {
            // Convert object to array if JSON returns objects
            if (is_object($course)) {
                $course = (array) $course;
            }

            $courses[] = [
                'courseId' => $course['courseId'],
                'courseCode' => $course['courseCode'],
            ];
        }

        return $courses;
    }

    public function getAllClassGroupCodes()
    {
        $groups = $this->classGroupApi->getAllClassGroups();

        $classGroupCodes = [];

        foreach ($groups as $group) {
            if (is_object($group)) {
                $group = (array) $group;
            }

            $classGroupCodes[] = [
                'classGroupId' => $group['classGroupId']
            ];
        }

        return $classGroupCodes;
    }


    public function getFilteredTimetableSlots($courseId, $classGroupId)
    {
        $courseSubjects = $this->courseSubjectmanagementApi->getCourseSubjectsByCourse($courseId);

        $slots = Timetable::with(['venue'])
            ->whereIn('courseSubjectId', $courseSubjects)
            ->where('classGroupId', 'LIKE', "%{$classGroupId}%")
            ->orderBy('dayOfWeek')
            ->orderBy('startTime')
            ->get();

        return $slots->map(function ($slot) {

            return [
                'timetableId' => $slot->timetableId,
                'subjectCode' => $slot->courseSubject->subject->subjectCode ?? 'N/A',
                'classType' => $slot->classType,
                'dayOfWeek' => $slot->dayOfWeek,
                'startTime' => Carbon::parse($slot->startTime)->format('H:i'),
                'venueId' => $slot->venueId,
            ];
        })->toArray();
    }

    public function updateTimeslot($originalTimetableId, $newData)
    {
        $originalSlot = Timetable::find($originalTimetableId);

        if (!$originalSlot) {
            return 'not_found';
        }

        $newStart = Carbon::createFromFormat('H:i', $newData['newStartTime']);
        $newEnd = $newStart->copy()->addHours(2)->format('H:i:s');
        $newStartFormatted = $newStart->format('H:i:s');
        $newDay = $newData['newDay'];
        $newVenue = $newData['newVenueId'];

        $isOccupied = Timetable::where('timetableId', '!=', $originalTimetableId)
            ->where('dayOfWeek', $newDay)
            ->where('venueId', $newVenue)
            ->where('startTime', '<', $newEnd)
            ->where('endTime', '>', $newStartFormatted)
            ->exists();

        if ($isOccupied) {
            return 'clash';
        }

        try {
            $originalSlot->update([
                'dayOfWeek' => $newDay,
                'venueId' => $newVenue,
                'startTime' => $newStartFormatted,
                'endTime' => $newEnd,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Update Timeslot DB Failure: ' . $e->getMessage());
            return 'error';
        }
    }

    // ======================================================================================================
    // ====================================== Select Timeslot ===============================================
    // ======================================================================================================

    public function getValidReplacementOptions($userId, $originalClassId)
    {
        $studentProfile = $this->userApi->getUser($userId);
        $originalClass = $this->timetableApi->getTimetableById($originalClassId);
        if (!$studentProfile || !$originalClass) {
            return collect([]);
        }

        $filterData = [
            'userId' => $userId,
            'originalClassId' => $originalClassId,
            'originalDate' => $originalClass['classDate'],
            'classGroupId' => $studentProfile['classGroupId'],
        ];

        // ReplacementController (validReplacementOptions)
        $validSlots = $this->replacementApi->getValidReplacementOptions($filterData);
        return $validSlots;
    }

    // ======================================================================================================
    // ====================================== Helper Methods ================================================
    // ======================================================================================================

    public function getAllClassGroups()
    {
        return $this->classGroupApi->getAllClassGroups();
    }

    public function getAllVenues()
    {
        return $this->venueApi->getAllVenues();
    }

    public function getClassDetails($originalClassId)
    {
        return $this->timetableApi->getTimetableById($originalClassId);
    }
}
