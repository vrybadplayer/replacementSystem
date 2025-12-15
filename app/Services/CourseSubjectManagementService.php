<?php

namespace App\Services;

/**
 * Minimal Coordinator Service for Course Subject Management Module
 * 
 * Routes facade calls to appropriate individual services
 * All business logic is in CourseService, SubjectService, and CourseSubjectService
 */
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

    // ==================== API-Based Methods ====================

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

