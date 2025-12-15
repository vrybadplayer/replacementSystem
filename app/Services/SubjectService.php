<?php

namespace App\Services;

use App\Models\Subject;

class SubjectService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    /**
     * Fetch subjects from API
     * 
     * @return array Raw API response data
     * @throws \Exception if API is unavailable
     */
    public function fetchSubjectsFromApi(): array
    {
        try {
            $url = "{$this->baseUrl}/subjects";
            \Illuminate\Support\Facades\Log::info('Fetching subjects from API', ['url' => $url]);
            
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                // Debug: Log the response structure
                \Illuminate\Support\Facades\Log::info('API response received', [
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
                    \Illuminate\Support\Facades\Log::warning('Unexpected API response format', ['data' => $data]);
                    return [];
                }
            } else {
                throw new \Exception('API returned unsuccessful response: ' . $response->status());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // API server is not available - throw exception instead of returning empty
            \Illuminate\Support\Facades\Log::error('Failed to connect to subjects API', [
                'url' => "{$this->baseUrl}/subjects",
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to connect to subjects API. Please ensure the API server is running on port 8001. Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching subjects from API', [
                'url' => "{$this->baseUrl}/subjects",
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Fetch course subjects from API to get course data
     * @return array Course subjects data
     */
    protected function fetchCourseSubjectsFromApi(): array
    {
        try {
            $url = "{$this->baseUrl}/courseSubject";
            \Illuminate\Support\Facades\Log::info('Fetching course subjects from API for subject list', ['url' => $url]);
            
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['data']) && is_array($data['data'])) {
                    return $data['data'];
                } elseif (is_array($data) && isset($data[0])) {
                    return $data;
                } else {
                    return [];
                }
            } else {
                \Illuminate\Support\Facades\Log::warning('Failed to fetch course subjects', ['status' => $response->status()]);
                return [];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Error fetching course subjects for subject list', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Format subjects data for views
     * @param array $data Processed data
     * @param array $courseSubjects Course subjects data from API
     * @return array Formatted data for view
     */
    public function formatSubjectsForView(array $data, array $courseSubjects = []): array
    {
        // Create a map of subjectId to course codes
        $subjectCoursesMap = [];
        foreach ($courseSubjects as $courseSubject) {
            $subjectId = $courseSubject['subjectId'] ?? null;
            $courseCode = $courseSubject['course']['courseId'] ?? $courseSubject['courseId'] ?? null;
            $courseName = $courseSubject['course']['courseName'] ?? null;
            
            if ($subjectId) {
                if (!isset($subjectCoursesMap[$subjectId])) {
                    $subjectCoursesMap[$subjectId] = [];
                }
                // Use courseId as course code, or courseName as fallback
                $courseDisplay = $courseCode ?? $courseName ?? 'N/A';
                if ($courseDisplay !== 'N/A' && !in_array($courseDisplay, $subjectCoursesMap[$subjectId])) {
                    $subjectCoursesMap[$subjectId][] = $courseDisplay;
                }
            }
        }

        return array_map(function ($subject) use ($subjectCoursesMap) {
            $subjectId = $subject['subjectId'] ?? '';
            $courseCodes = $subjectCoursesMap[$subjectId] ?? [];
            $courseDisplay = !empty($courseCodes) ? implode(', ', $courseCodes) : 'N/A';
            
            return [
                'id' => $subjectId,
                'code' => $subject['subjectCode'] ?? '',
                'name' => $subject['subjectName'] ?? '',
                'credits' => $subject['creditHours'] ?? 0,
                'course' => $courseDisplay,
                'lecturer' => $subject['lecturer'] ?? null,
                'type' => $subject['category'] ?? '',
                'status' => $subject['status'] ?? 'active',
            ];
        }, $data);
    }

    /**
     * Fetch from API and format for views
     * ONLY fetches from external API (no database fallback)
     * Uses CourseSubject API to get course data
     * 
     * @return array Formatted subjects for view
     * @throws \Exception if API is unavailable
     */
    public function getSubjectsForViewFromApi(): array
    {
        $apiData = $this->fetchSubjectsFromApi();
        $courseSubjects = $this->fetchCourseSubjectsFromApi();

        return $this->formatSubjectsForView($apiData, $courseSubjects);
    }

    /**
     * Create or update a subject in the database
     * 
     * @param array $data Subject data
     * @param string|null $subjectId Subject ID (if null, will use data['subjectId'])
     * @return Subject
     */
    public function createOrUpdateSubject(array $data, ?string $subjectId = null): Subject
    {
        $subjectId = $subjectId ?? $data['subjectId'] ?? null;
        
        if (!$subjectId) {
            throw new \InvalidArgumentException('Subject ID is required');
        }

        return Subject::updateOrCreate(
            ['subjectId' => $subjectId],
            $data
        );
    }
}

