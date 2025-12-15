<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Http;

class CourseService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    /**
     * Fetch courses from API
     * 
     * @return array Raw API response data
     * @throws \Exception if API is unavailable
     */
    public function fetchCoursesFromApi(): array
    {
        try {
            $url = "{$this->baseUrl}/courses";
            \Illuminate\Support\Facades\Log::info('Fetching courses from API', ['url' => $url]);
            
            $response = Http::timeout(5)->get($url);

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
            \Illuminate\Support\Facades\Log::error('Failed to connect to courses API', [
                'url' => "{$this->baseUrl}/courses",
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to connect to courses API. Please ensure the API server is running on port 8001. Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching courses from API', [
                'url' => "{$this->baseUrl}/courses",
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Format courses data for views
     * 
     * @param array $data Processed data
     * @return array Formatted data for view
     */
    public function formatCoursesForView(array $data): array
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
     * Fetch from API and format for views
     * ONLY fetches from external API (no database fallback)
     * 
     * @return array Formatted courses for view
     * @throws \Exception if API is unavailable
     */
    public function getCoursesForViewFromApi(): array
    {
        // Fetch data from API and format directly for views
        $apiData = $this->fetchCoursesFromApi();
        return $this->formatCoursesForView($apiData);
    }
}

