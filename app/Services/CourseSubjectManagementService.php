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
    public function fetchCoursesFromApi()
    {
        return $this->courseService->fetchCoursesFromApi();
    }

    public function getCoursesForViewFromApi()
    {
        return $this->courseService->getCoursesForViewFromApi();
    }

    public function getCourseByIdFromApi(string $courseId)
    {
        return $this->courseService->fetchCourseByIdFromApi($courseId);
    }
    
    public function createOrUpdateCourse(array $data, ?string $courseId = null)
    {
        return $this->courseService->createOrUpdateCourse($data, $courseId);
    }


    // Subject Service
    public function fetchSubjectsFromApi()
    {
        return $this->subjectService->fetchSubjectsFromApi();
    }

    public function getSubjectsForViewFromApi()
    {
        return $this->subjectService->getSubjectsForViewFromApi();
    }

    public function getSubjectByIdFromApi(string $subjectId)
    {
        return $this->subjectService->fetchSubjectByIdFromApi($subjectId);
    }

    public function createOrUpdateSubject(array $data, ?string $subjectId = null)
    {
        return $this->subjectService->createOrUpdateSubject($data, $subjectId);
    }

    public function getSubjectByCourseSubject(string $courseSubjectId)
    {
        return $this->subjectService->getSubjectByCourseSubject($courseSubjectId);
    }


    // Course Subject Service
    public function fetchCourseSubjectsFromApi()
    {
        return $this->courseSubjectService->fetchCourseSubjectsFromApi();
    }

    public function getCourseSubjectByIdFromApi(string $courseSubjectId)
    {
        return $this->courseSubjectService->fetchCourseSubjectByIdFromApi($courseSubjectId);
    }

    public function createOrUpdateCourseSubject(array $data, ?string $courseSubjectId = null)
    {
        return $this->courseSubjectService->createOrUpdateCourseSubject($data, $courseSubjectId);
    }

    public function getCourseSubjectsByCourse(string $courseId)
    {
        return $this->courseSubjectService->getCourseSubjectsByCourse($courseId);
    }

    public function getCourseSubjectCodes(string $courseSubjectId)
    {
        return $this->courseSubjectService->getCourseSubjectCodes($courseSubjectId);
    }
}

