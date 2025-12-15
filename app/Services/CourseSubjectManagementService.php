<?php

namespace App\Services;

class CourseSubjectManagementService
{
    protected CourseService $courseService;
    protected SubjectService $subjectService;
    protected CourseSubjectService $courseSubjectService;

    public function __construct(
        CourseService $courseService,
        SubjectService $subjectService,
        CourseSubjectService $courseSubjectService
    ) {
        $this->courseService = $courseService;
        $this->subjectService = $subjectService;
        $this->courseSubjectService = $courseSubjectService;
    }

    public function fetchCoursesFromApi(): array
    {
        return $this->courseService->fetchCoursesFromApi();
    }

    public function getCoursesForViewFromApi(): array
    {
        return $this->courseService->getCoursesForViewFromApi();
    }

    public function fetchSubjectsFromApi(): array
    {
        return $this->subjectService->fetchSubjectsFromApi();
    }

    public function getSubjectsForViewFromApi(): array
    {
        return $this->subjectService->getSubjectsForViewFromApi();
    }
}

