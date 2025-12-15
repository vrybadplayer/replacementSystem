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
     * Format subjects data for views
     * @param array $data Processed data
     * @return array Formatted data for view
     */
    public function formatSubjectsForView(array $data): array
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
     * @return array Formatted subjects for view
     * @throws \Exception if API is unavailable
     */
    public function getSubjectsForViewFromApi(): array
    {
        // Fetch data from API and format directly for views
        $apiData = $this->fetchSubjectsFromApi();
        return $this->formatSubjectsForView($apiData);
    }
}

