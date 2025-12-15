<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

/**
 * Unified Service for Course Subject Management Module
 * 
 * Aggregates Course, Subject, and CourseSubject services
 * Provides a single entry point for all course management operations
 */
class CourseSubjectManagementService
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
        $this->baseUrl = 'http://127.0.0.1:8001/api';
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
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/courses");

            if ($response->successful()) {
                $json = $response->json();
                return $json['data'] ?? [];
            }
        } catch (ConnectionException $e) {
            // API server is not available
            Log::warning('Failed to connect to courses API', [
                'url' => "{$this->baseUrl}/courses",
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching courses from API', [
                'url' => "{$this->baseUrl}/courses",
                'error' => $e->getMessage()
            ]);
        }

        return [];
    }

    /**
     * Step 2-4: Fetch from API, process data, format for views
     * Falls back to database if API is unavailable
     * 
     * @param array|null $filters Optional filters to apply
     * @return array Formatted courses for view
     */
    public function getCoursesForViewFromApi(array $filters = null): array
    {
        // Step 1: Fetch data from API
        $apiData = $this->fetchCoursesFromApi();

        // If API returned no data (empty or connection failed), fall back to database
        if (empty($apiData)) {
            return $this->getCoursesForView();
        }

        // Format directly for views (no processing step needed)
        return $this->formatCoursesForView($apiData);
    }

    /**
     * Format courses data for views
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
        try {
            $url = "{$this->baseUrl}/subjects";
            Log::info('Fetching subjects from API', ['url' => $url]);
            
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                // Debug: Log the response structure
                Log::info('API response received', [
                    'status' => $response->status(),
                    'has_data_key' => isset($data['data']),
                    'data_type' => gettype($data['data'] ?? null),
                    'data_count' => isset($data['data']) && is_array($data['data']) ? count($data['data']) : 0,
                    'response_keys' => array_keys($data ?? [])
                ]);
                
                // Handle different response formats
                if (isset($data['data']) && is_array($data['data'])) {
                    // Standard format: { "success": true, "data": [...] }
                    return $data['data'];
                } elseif (is_array($data) && isset($data[0])) {
                    // Direct array format: [...]
                    return $data;
                } else {
                    // Empty or unexpected format
                    Log::warning('Unexpected API response format', ['data' => $data]);
                    return [];
                }
            } else {
                throw new \Exception('API returned unsuccessful response: ' . $response->status());
            }
        } catch (ConnectionException $e) {
            // API server is not available - throw exception instead of returning empty
            Log::error('Failed to connect to subjects API', [
                'url' => "{$this->baseUrl}/subjects",
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to connect to subjects API. Please ensure the API server is running on port 8001. Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error fetching subjects from API', [
                'url' => "{$this->baseUrl}/subjects",
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Format subjects data for views
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
     * Fetch from API and format for views
     * ONLY fetches from external API (no database fallback)
     * 
     * @param array|null $filters Optional filters to apply (currently not used)
     * @return array Formatted subjects for view
     * @throws \Exception if API is unavailable
     */
    public function getSubjectsForViewFromApi(array $filters = null): array
    {
        // Fetch data from API and format directly for views
        $apiData = $this->fetchSubjectsFromApi();
        return $this->formatSubjectsForView($apiData);
    }

    // ==================== Subject Methods ====================

    /**
     * Get all subjects via API
     * Falls back to database if API is unavailable
     * Uses fetchSubjectsFromApi() to avoid code duplication
     */
    public function getAllSubjects()
    {
        try {
            // Use fetchSubjectsFromApi() to avoid duplicating HTTP call logic
            $apiData = $this->fetchSubjectsFromApi();
            
            // Convert array to collection format
            return collect($apiData)->map(function ($item) {
                return (object) $item;
            });
        } catch (\Exception $e) {
            // API server is not available, fall back to database
            Log::warning('Failed to fetch subjects from API, falling back to database', [
                'url' => "{$this->baseUrl}/subjects",
                'error' => $e->getMessage()
            ]);
            return $this->subjectService->getAllSubjects();
        }
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
     * Falls back to database if API is unavailable
     */
    public function createSubject(array $data)
    {
        try {
            $response = Http::timeout(5)->post("{$this->baseUrl}/subjects", $data);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    return (object) $responseData['data'];
                }
            }
        } catch (ConnectionException $e) {
            // API server is not available, fall back to database
            Log::warning('Failed to connect to subjects API, falling back to database', [
                'url' => "{$this->baseUrl}/subjects",
                'error' => $e->getMessage()
            ]);
            return $this->subjectService->createSubject($data);
        } catch (\Exception $e) {
            Log::error('Error creating subject via API, falling back to database', [
                'url' => "{$this->baseUrl}/subjects",
                'error' => $e->getMessage()
            ]);
            return $this->subjectService->createSubject($data);
        }

        throw new \Exception('Failed to create subject via API');
    }

    /**
     * Update a subject via API
     * Falls back to database if API is unavailable
     */
    public function updateSubject(string $subjectId, array $data)
    {
        try {
            $response = Http::timeout(5)->put("{$this->baseUrl}/subjects/{$subjectId}", $data);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    return (object) $responseData['data'];
                }
            }

            if ($response->status() === 404) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Subject not found');
            }
        } catch (ConnectionException $e) {
            // API server is not available, fall back to database
            Log::warning('Failed to connect to subjects API, falling back to database', [
                'url' => "{$this->baseUrl}/subjects/{$subjectId}",
                'error' => $e->getMessage()
            ]);
            return $this->subjectService->updateSubject($subjectId, $data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating subject via API, falling back to database', [
                'url' => "{$this->baseUrl}/subjects/{$subjectId}",
                'error' => $e->getMessage()
            ]);
            return $this->subjectService->updateSubject($subjectId, $data);
        }

        throw new \Exception('Failed to update subject via API');
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

