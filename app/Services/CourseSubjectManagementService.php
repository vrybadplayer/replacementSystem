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

    // Course Service
    public function fetchCoursesFromApi(): array
    {
        return $this->courseService->fetchCoursesFromApi();
    }

    public function getCoursesForViewFromApi(): array
    {
        return $this->courseService->getCoursesForViewFromApi();
    }

    public function getCourseByIdFromApi(string $courseId): array
    {
        return $this->courseService->fetchCourseByIdFromApi($courseId);
    }
    
    public function createOrUpdateCourse(array $data, ?string $courseId = null): array
    {
        return $this->courseService->createOrUpdateCourse($data, $courseId);
    }


    // Subject Service
    public function fetchSubjectsFromApi(): array
    {
        return $this->subjectService->fetchSubjectsFromApi();
    }

    public function getSubjectsForViewFromApi(): array
    {
        return $this->subjectService->getSubjectsForViewFromApi();
    }

    public function getSubjectByIdFromApi(string $subjectId): array
    {
        return $this->subjectService->fetchSubjectByIdFromApi($subjectId);
    }

    public function createOrUpdateSubject(array $data, ?string $subjectId = null): array
    {
        return $this->subjectService->createOrUpdateSubject($data, $subjectId);
    }


    // Course Subject Service
    public function fetchCourseSubjectsFromApi(): array
    {
        return $this->courseSubjectService->fetchCourseSubjectsFromApi();
    }

    public function getCourseSubjectByIdFromApi(string $courseSubjectId): array
    {
        return $this->courseSubjectService->fetchCourseSubjectByIdFromApi($courseSubjectId);
    }

    public function createOrUpdateCourseSubject(array $data, ?string $courseSubjectId = null): array
    {
        return $this->courseSubjectService->createOrUpdateCourseSubject($data, $courseSubjectId);
    }
}

