<?php

namespace App\Mediator;

interface TimetableMediatorInterface {

    // For Students
    public function getCurrentWeekSchedule($studentId, $selectedDate);
    public function getTransitiveSubjectList();
    public function getReplacementsByDateRange($userId, $startOfWeek, $endOfWeek);
    public function findTimetableForReplacement($requestedDate, $startTime, $endTime, $requestedClassGroupId);

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

    // For Student Select Timeslot Page
    public function getValidReplacementOptions($userId, $originalClassId);
    public function getClassDetails($originalClassId);

    // Helper Functions
    public function getAllClassGroups();
    public function getAllVenues();

}

?>