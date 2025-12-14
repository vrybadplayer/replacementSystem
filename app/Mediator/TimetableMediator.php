<?php

namespace App\Mediator;

use App\Models\Timetable;
use App\Models\Venue;
use App\Models\ClassGroup;

use App\Services\CourseService;

class TimetableMediator implements TimetableMediatorInterface
{
    // Assume the Course Service API is stored here
    protected $courseApi;

    public function __construct(CourseService $courseApi)
    {
        $this->courseApi = $courseApi;
    }

    // ---------- TEST API ----------
    public function testApi() {
        $data = $this->courseApi->fetchCourses();
        return $data;
    }
    // ---------- TEST API ----------

    // For Students
    public function getStudentSchedule($studentId)
    {

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