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

    public function createCourseViaApi(array $data)
    {
        return $this->courseService->createCourseViaApi($data);
    }

    public function updateCourseViaApi(string $courseId, array $data)
    {
        return $this->courseService->updateCourseViaApi($courseId, $data);
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

    public function createSubjectViaApi(array $data)
    {
        return $this->subjectService->createSubjectViaApi($data);
    }

    public function updateSubjectViaApi(string $subjectId, array $data)
    {
        return $this->subjectService->updateSubjectViaApi($subjectId, $data);
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
    
    public function getCourseSubjectCodes(string $courseSubjectId)
    {
        return $this->courseSubjectService->getCourseSubjectCodes($courseSubjectId);
    }

    public function getCourseSubjectByIdFromApi(string $courseSubjectId)
    {
        return $this->courseSubjectService->fetchCourseSubjectByIdFromApi($courseSubjectId);
    }

    public function createCourseSubjectViaApi(array $data)
    {
        return $this->courseSubjectService->createCourseSubjectViaApi($data);
    }

    public function updateCourseSubjectViaApi(string $courseSubjectId, array $data)
    {
        return $this->courseSubjectService->updateCourseSubjectViaApi($courseSubjectId, $data);
    }

    public function getCourseSubjectsByCourse(string $courseId)
    {
        return $this->courseSubjectService->getCourseSubjectsByCourse($courseId);
    }

    public function fetchCourseSubjectWithForeignKeys($subjectId, $courseId)
    {
        return $this->courseSubjectService->fetchCourseSubjectWithForeignKeys($subjectId, $courseId);
    }
}

