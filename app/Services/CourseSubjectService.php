<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\CourseSubject;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseSubjectService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    /**
     * Fetch course subjects from API
     * 
     * @return array Raw API response data
     * @throws \Exception if API is unavailable
     */
    public function fetchCourseSubjectsFromApi(): array
    {
        try {
            $url = "{$this->baseUrl}/courseSubject";
            \Illuminate\Support\Facades\Log::info('Fetching course subjects from API', ['url' => $url]);
            
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
            \Illuminate\Support\Facades\Log::error('Failed to connect to course subjects API', [
                'url' => "{$this->baseUrl}/courseSubject",
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to connect to course subjects API. Please ensure the API server is running on port 8001. Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching course subjects from API', [
                'url' => "{$this->baseUrl}/courseSubject",
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create or update a course subject in the database
     * 
     * @param array $data Course subject data
     * @param string|null $courseSubjectId Course subject ID (if null, will use data['courseSubjectId'])
     * @return CourseSubject
     */
    public function createOrUpdateCourseSubject(array $data, ?string $courseSubjectId = null): CourseSubject
    {
        $courseSubjectId = $courseSubjectId ?? $data['courseSubjectId'] ?? null;
        
        if (!$courseSubjectId) {
            throw new \InvalidArgumentException('Course Subject ID is required');
        }

        return CourseSubject::updateOrCreate(
            ['courseSubjectId' => $courseSubjectId],
            $data
        );
    }
}