<?php

namespace App\Mediator;

interface TimetableMediatorInterface {

    // For Students
    public function getCurrentWeekSchedule($studentId, $selectedDate);
    //public function getValidReplacementTimeslots($studentId, $subjectId);
    public function getTransitiveSubjectList();

    // For Admin Subject Assignment
    public function getAllCourses();
    public function getAllSubjects();
    public function getAvailableTimeslots($day, $venueId, $duration);
    public function assignClass($data);

    // For Admin Timeslot Management
    public function getAllCourseCodes();
    public function getAllClassGroupCodes();
    public function getFilteredTimetableSlots($courseId, $classGroupId);
    public function updateTimeslot($originalTimetableId, $newData);

    // Helper Functions
    public function getAllClassGroups();
    public function getAllVenues();

}

?>