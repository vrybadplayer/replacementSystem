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


    public function fetchCourseSubjectsFromApi(): array
    {
        return $this->courseSubjectService->fetchCourseSubjectsFromApi();
    }

    /**
     * Create or update a course in the database
     * 
     * @param array $data Course data
     * @param string|null $courseId Course ID
     * @return \App\Models\Course
     */
    public function createOrUpdateCourse(array $data, ?string $courseId = null): \App\Models\Course
    {
        return $this->courseService->createOrUpdateCourse($data, $courseId);
    }

    /**
     * Create or update a subject in the database
     * 
     * @param array $data Subject data
     * @param string|null $subjectId Subject ID
     * @return \App\Models\Subject
     */
    public function createOrUpdateSubject(array $data, ?string $subjectId = null): \App\Models\Subject
    {
        return $this->subjectService->createOrUpdateSubject($data, $subjectId);
    }

    /**
     * Create or update a course subject in the database
     * 
     * @param array $data Course subject data
     * @param string|null $courseSubjectId Course subject ID
     * @return \App\Models\CourseSubject
     */
    public function createOrUpdateCourseSubject(array $data, ?string $courseSubjectId = null): \App\Models\CourseSubject
    {
        return $this->courseSubjectService->createOrUpdateCourseSubject($data, $courseSubjectId);
    }
}

