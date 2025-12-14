<?php

namespace App\Mediator;

interface TimetableMediatorInterface {

    // For Students
    public function getCurrentWeekSchedule($studentId, $selectedDate);
    public function getValidReplacementTimeslots($studentId, $subjectId);
    public function getTransitiveSubjectList();

    // For Admin Subject Assignment
    public function populateCourseDropdown();
    public function filterTimeslots($filters);
    public function assignClass($data);
    public function updateClass($id, $data);
    public function deleteClass($id);

    // For API
    public function getClassDetails($timetableId);
    public function createReplacementSlot($originalId, $date, $time);
}

?>