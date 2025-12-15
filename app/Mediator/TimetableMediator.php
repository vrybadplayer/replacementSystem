<?php

namespace App\Mediator;
use Carbon\Carbon;
use App\Models\Timetable;
use App\Models\Venue;
use App\Models\ClassGroup;
use App\Models\CourseSubject;
use App\Services\CourseManagementService;
use App\Services\CourseSubjectService;
use App\Services\UserService;
use Illuminate\Support\Facades\DB; 

class TimetableMediator implements TimetableMediatorInterface
{
    // Assume the Course Service API is stored here
    protected $courseApi;
    protected $courseSubjectApi;
    protected $userApi;

    public function __construct(CourseManagementService $courseApi, CourseSubjectService $courseSubjectApi, UserService $userApi)
    {
        $this->courseApi = $courseApi;
        $this->courseSubjectApi = $courseSubjectApi;
        $this->userApi = $userApi;
    }

    // For Students
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

            //dd($classes);

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


    public function getValidReplacementTimeslots($studentId, $subjectId) {

    }
    
    // For Admin Subject Assignment
    public function populateCourseDropdown() {

    }

    public function filterTimeslots($filters) {

    }

    public function assignClass($data) {

    }

    public function updateClass($id, $data) {

    }

    public function deleteClass($id) {

    }


    // For API
    public function getClassDetails($timetableId) {

    }

    public function createReplacementSlot($originalId, $date, $time) {

    }

}
