<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Unified Service for Course Management Module
 * 
 * Aggregates Course, Subject, and CourseSubject services
 * Provides a single entry point for all course management operations
 */
class CourseManagementService
{
    protected CourseService $courseService;
    protected SubjectService $subjectService;
    protected CourseSubjectService $courseSubjectService;
    protected string $baseUrl;

    public function __construct(
        CourseService $courseService,
        SubjectService $subjectService,
        CourseSubjectService $courseSubjectService
    ) {
        $this->courseService = $courseService;
        $this->subjectService = $subjectService;
        $this->courseSubjectService = $courseSubjectService;
        // API base URL - matches CourseService pattern for XAMPP setup
        $this->baseUrl = 'http://127.0.0.1:8001/replacementSystem/public/api';
    }

    // ==================== Course Methods ====================

    /**
     * Get all courses
     */
    public function getAllCourses()
    {
        return $this->courseService->getAllCourses();
    }

    /**
     * Get course by ID
     */
    public function getCourseById(string $courseId)
    {
        return $this->courseService->getCourseById($courseId);
    }

    /**
     * Create or update a course
     */
    public function createOrUpdateCourse(array $data, string $courseId = null)
    {
        return $this->courseService->createOrUpdateCourse($data, $courseId);
    }

    /**
     * Create a new course
     */
    public function createCourse(array $data)
    {
        return $this->courseService->createCourse($data);
    }

    /**
     * Update a course
     */
    public function updateCourse(string $courseId, array $data)
    {
        return $this->courseService->updateCourse($courseId, $data);
    }

    /**
     * Delete a course
     */
    public function deleteCourse(string $courseId)
    {
        return $this->courseService->deleteCourse($courseId);
    }

    /**
     * Get courses formatted for views
     */
    public function getCoursesForView()
    {
        return $this->courseService->getCoursesForView();
    }

    // ==================== API-Based Methods ====================

    /**
     * Step 1: Fetch courses from API
     * 
     * @return array Raw API response data
     */
    public function fetchCoursesFromApi(): array
    {
        $response = Http::get("{$this->baseUrl}/courses");

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Step 2-4: Fetch from API, process data, format for views
     * 
     * @param array|null $filters Optional filters to apply
     * @return array Formatted courses for view
     */
    public function getCoursesForViewFromApi(array $filters = null): array
    {
        // Step 1: Fetch data from API
        $apiData = $this->fetchCoursesFromApi();

        // Step 2: Process data (filtering, transformation, etc.)
        $processedData = $this->processCoursesData($apiData, $filters);

        // Step 3: Format for views
        return $this->formatCoursesForView($processedData);
    }

    /**
     * Step 2: Process courses data (filtering, validation, etc.)
     * 
     * @param array $data Raw API data
     * @param array|null $filters Optional filters
     * @return array Processed data
     */
    protected function processCoursesData(array $data, ?array $filters = null): array
    {
        // Apply filters if provided
        if ($filters) {
            $data = $this->applyFilters($data, $filters);
        }

        // Additional processing logic can be added here
        // e.g., validation, data enrichment, etc.

        return $data;
    }

    /**
     * Step 3: Format courses data for views
     * 
     * @param array $data Processed data
     * @return array Formatted data for view
     */
    protected function formatCoursesForView(array $data): array
    {
        return array_map(function ($course) {
            return [
                'id' => $course['courseId'] ?? '',
                'code' => $course['courseId'] ?? '',
                'name' => $course['courseName'] ?? '',
                'faculty' => $course['faculty'] ?? 'N/A',
                'status' => $course['status'] ?? 'active',
            ];
        }, $data);
    }

    /**
     * Fetch subjects from API
     * 
     * @return array Raw API response data
     */
    public function fetchSubjectsFromApi(): array
    {
        $response = Http::get("{$this->baseUrl}/subjects");

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Fetch from API, process data, format for views
     * 
     * @param array|null $filters Optional filters to apply
     * @return array Formatted subjects for view
     */
    public function getSubjectsForViewFromApi(array $filters = null): array
    {
        // Step 1: Fetch data from API
        $apiData = $this->fetchSubjectsFromApi();

        // Step 2: Process data (filtering, transformation, etc.)
        $processedData = $this->processSubjectsData($apiData, $filters);

        // Step 3: Format for views
        return $this->formatSubjectsForView($processedData);
    }

    /**
     * Process subjects data (filtering, validation, etc.)
     * 
     * @param array $data Raw API data
     * @param array|null $filters Optional filters
     * @return array Processed data
     */
    protected function processSubjectsData(array $data, ?array $filters = null): array
    {
        // Apply filters if provided
        if ($filters) {
            $data = $this->applyFilters($data, $filters);
        }

        // Additional processing logic can be added here

        return $data;
    }

    /**
     * Format subjects data for views
     * 
     * @param array $data Processed data
     * @return array Formatted data for view
     */
    protected function formatSubjectsForView(array $data): array
    {
        return array_map(function ($subject) {
            return [
                'id' => $subject['subjectId'] ?? '',
                'code' => $subject['subjectCode'] ?? '',
                'name' => $subject['subjectName'] ?? '',
                'credits' => $subject['creditHours'] ?? 0,
                'course' => $subject['courseCode'] ?? 'N/A',
                'lecturer' => $subject['lecturer'] ?? null,
                'type' => $subject['category'] ?? '',
                'status' => $subject['status'] ?? 'active',
            ];
        }, $data);
    }

    /**
     * Apply filters to data array
     * 
     * @param array $data Data to filter
     * @param array $filters Filters to apply
     * @return array Filtered data
     */
    protected function applyFilters(array $data, array $filters): array
    {
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $data = array_filter($data, function ($item) use ($key, $value) {
                    return isset($item[$key]) && 
                           (stripos($item[$key], $value) !== false || $item[$key] == $value);
                });
            }
        }

        return array_values($data);
    }

    // ==================== Subject Methods ====================

    /**
     * Get all subjects via API
     */
    public function getAllSubjects()
    {
        $response = Http::get("{$this->baseUrl}/subjects");

        if ($response->successful()) {
            $data = $response->json();
            // Convert API response to collection format
            if (isset($data['data']) && is_array($data['data'])) {
                return collect($data['data'])->map(function ($item) {
                    return (object) $item;
                });
            }
        }

        return collect([]);
    }

    /**
     * Get subject by ID via API
     */
    public function getSubjectById(string $subjectId)
    {
        $response = Http::get("{$this->baseUrl}/subjects/{$subjectId}");

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['data']) && is_array($data['data'])) {
                return (object) $data['data'];
            }
        }

        return null;
    }

    /**
     * Create or update a subject
     */
    public function createOrUpdateSubject(array $data, string $subjectId = null)
    {
        return $this->subjectService->createOrUpdateSubject($data, $subjectId);
    }

    /**
     * Create a new subject via API
     */
    public function createSubject(array $data)
    {
        $response = Http::post("{$this->baseUrl}/subjects", $data);

        if ($response->successful()) {
            $responseData = $response->json();
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                return (object) $responseData['data'];
            }
        }

        throw new \Exception('Failed to create subject via API');
    }

    /**
     * Update a subject via API
     */
    public function updateSubject(string $subjectId, array $data)
    {
        $response = Http::put("{$this->baseUrl}/subjects/{$subjectId}", $data);

        if ($response->successful()) {
            $responseData = $response->json();
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                return (object) $responseData['data'];
            }
        }

        if ($response->status() === 404) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Subject not found');
        }

        throw new \Exception('Failed to update subject via API');
    }

    /**
     * Delete a subject via API
     */
    public function deleteSubject(string $subjectId)
    {
        $response = Http::delete("{$this->baseUrl}/subjects/{$subjectId}");

        if ($response->successful()) {
            $responseData = $response->json();
            return isset($responseData['success']) && $responseData['success'] === true;
        }

        if ($response->status() === 404) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Subject not found');
        }

        return false;
    }

    /**
     * Get subjects formatted for views
     */
    public function getSubjectsForView()
    {
        return $this->subjectService->getSubjectsForView();
    }

    /**
     * Fetch subjects with courses relationship loaded
     * 
     * @param array|null $filters Optional filters to apply
     * @return \Illuminate\Database\Eloquent\Collection Subjects with courses relationship
     */
    public function fetchSubjectsWithCourses(array $filters = null)
    {
        // Get all subjects via API
        $subjects = $this->getAllSubjects();

        // Apply filters if provided
        if ($filters) {
            $subjects = $this->applySubjectFilters($subjects, $filters);
        }

        return $subjects;
    }

    /**
     * Optionally prepare course codes for subjects
     * Formats course codes as comma-separated string
     * 
     * @param \Illuminate\Database\Eloquent\Collection $subjects
     * @return \Illuminate\Database\Eloquent\Collection Subjects with prepared course codes
     */
    public function prepareCourseCodes($subjects)
    {
        return $subjects->map(function ($subject) {
            // Ensure courses relationship is loaded
            if (!$subject->relationLoaded('courses')) {
                $subject->load('courses');
            }

            // Prepare course codes attribute
            $courses = $subject->courses;
            $subject->courseCodes = $courses->isNotEmpty() 
                ? $courses->pluck('courseCode')->join(', ')
                : 'N/A';

            return $subject;
        });
    }

    /**
     * Single point to get subjects with courses for view
     * Returns subjects list with course information included
     * 
     * @param array|null $filters Optional filters to apply
     * @return array Formatted subjects with courses for view
     */
    public function getSubjectsWithCoursesForView(array $filters = null): array
    {
        // Fetch subjects with courses
        $subjects = $this->fetchSubjectsWithCourses($filters);
        
        // Prepare course codes
        $subjects = $this->prepareCourseCodes($subjects);

        // Format subjects with course information
        return $subjects->map(function ($subject) {
            return [
                'id' => $subject->subjectId,
                'code' => $subject->subjectCode ?? '',
                'name' => $subject->subjectName ?? '',
                'credits' => $subject->creditHours ?? 0,
                'course' => $subject->courseCodes ?? 'N/A',
                'lecturer' => null,
                'type' => $subject->category ?? '',
                'status' => $subject->status ?? 'active',
            ];
        })->toArray();
    }

    /**
     * Apply filters to subjects collection
     * 
     * @param \Illuminate\Database\Eloquent\Collection $subjects
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function applySubjectFilters($subjects, array $filters)
    {
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $subjects = $subjects->filter(function ($subject) use ($key, $value) {
                    // Handle different filter types
                    // Note: $subject is now an object from API, not Eloquent model
                    switch ($key) {
                        case 'status':
                            return isset($subject->status) && $subject->status === $value;
                        case 'category':
                            return isset($subject->category) && $subject->category === $value;
                        case 'course':
                            // Filter by course code (if courseCode exists in API response)
                            return isset($subject->courseCode) && stripos($subject->courseCode, $value) !== false;
                        case 'search':
                            // Search in subject name or code
                            $subjectName = $subject->subjectName ?? '';
                            $subjectCode = $subject->subjectCode ?? '';
                            return stripos($subjectName, $value) !== false 
                                || stripos($subjectCode, $value) !== false;
                        default:
                            return true;
                    }
                });
            }
        }

        return $subjects->values();
    }

    // ==================== CourseSubject Methods ====================

    /**
     * Get all course subjects
     */
    public function getAllCourseSubjects()
    {
        return $this->courseSubjectService->getAllCourseSubjects();
    }

    /**
     * Get course subject by ID
     */
    public function getCourseSubjectById(string $courseSubjectId)
    {
        return $this->courseSubjectService->getCourseSubjectById($courseSubjectId);
    }

    /**
     * Create or update a course subject
     */
    public function createOrUpdateCourseSubject(array $data, string $courseSubjectId = null)
    {
        return $this->courseSubjectService->createOrUpdateCourseSubject($data, $courseSubjectId);
    }

    /**
     * Create a new course subject
     */
    public function createCourseSubject(array $data)
    {
        return $this->courseSubjectService->createCourseSubject($data);
    }

    /**
     * Update a course subject
     */
    public function updateCourseSubject(string $courseSubjectId, array $data)
    {
        return $this->courseSubjectService->updateCourseSubject($courseSubjectId, $data);
    }

    /**
     * Delete a course subject
     */
    public function deleteCourseSubject(string $courseSubjectId)
    {
        return $this->courseSubjectService->deleteCourseSubject($courseSubjectId);
    }
}
