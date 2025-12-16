<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\ReplacementAppeal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReplacementService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    public function __construct()
    {
        // Base URL for API calls
    }

    /**
     * Get single timetable record by ID from API
     * Returns Eloquent model with relationships loaded
     */
    public function getTimetableByIdFromApi($timetableId)
    {
        try {
            $url = "{$this->baseUrl}/timetable/{$timetableId}";
            Log::info('Fetching timetable by ID from API', ['url' => $url, 'id' => $timetableId]);
            
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $classData = $data['data'] ?? null;
                
                if ($classData && isset($classData['timetableId'])) {
                    // Load full Eloquent model with relationships from database
                    return Timetable::with(['subject', 'venue', 'classGroup'])
                        ->find($classData['timetableId']);
                }
                
                return null;
            }
            
            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('Failed to fetch timetable by ID from API', [
                'url' => $url ?? "{$this->baseUrl}/timetable/{$timetableId}",
                'id' => $timetableId,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to connect to API. Please ensure the API server is running on port 8001.');
        }
    }

    /**
     * Get all available classes for replacement from Timetable API
     * Calls the timetable module's API and filters for future classes
     * Returns Eloquent models with relationships loaded
     */
    public function getAvailableClassesFromApi()
    {
        try {
            // Call the Timetable API instead of replacement-specific endpoint
            $url = "{$this->baseUrl}/timetable";
            Log::info('Fetching timetable from Timetable API', ['url' => $url]);
            
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $classesData = collect($data['data'] ?? []);
                
                // Filter for future classes only
                $futureClasses = $classesData->filter(function($class) {
                    $classDate = is_array($class) ? ($class['classDate'] ?? null) : ($class->classDate ?? null);
                    return $classDate && $classDate >= now()->format('Y-m-d');
                });
                
                // If we have data, load full Eloquent models with relationships from database
                if ($futureClasses->isNotEmpty()) {
                    $classIds = $futureClasses->pluck('timetableId')->toArray();
                    return Timetable::with(['subject', 'venue', 'classGroup'])
                        ->whereIn('timetableId', $classIds)
                        ->where('classDate', '>=', now()->format('Y-m-d'))
                        ->orderBy('classDate')
                        ->orderBy('startTime')
                        ->get();
                }
                
                return collect([]);
            }
            
            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('Failed to fetch timetable from API', [
                'url' => $url ?? "{$this->baseUrl}/timetable",
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to connect to API. Please ensure the API server is running on port 8001.');
        }
    }

    /**
     * Get user's appeals from API
     * Returns collection with nested objects for view compatibility
     */
    public function getUserAppealsFromApi($userId)
    {
        try {
            $url = "{$this->baseUrl}/replacement/appeals/user/{$userId}";
            Log::info('Fetching user appeals from API', ['url' => $url, 'userId' => $userId]);
            
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $appealsData = $data['data'] ?? [];
                
                // Convert arrays to objects recursively for view compatibility
                return collect($appealsData)->map(function($appeal) {
                    return $this->arrayToObject($appeal);
                });
            }
            
            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('Failed to fetch user appeals from API', [
                'url' => $url ?? "{$this->baseUrl}/replacement/appeals/user/{$userId}",
                'userId' => $userId,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to connect to API. Please ensure the API server is running on port 8001.');
        }
    }

    /**
     * Get single appeal from API
     */
    public function getAppealFromApi($appealId)
    {
        try {
            $response = Http::get($this->baseUrl . '/replacement/appeals/' . $appealId);
            
            if ($response->successful() && $response->json('success')) {
                return $this->arrayToObject($response->json('data'));
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get all appeals from API
     * Returns collection with nested objects for view compatibility
     */
    public function getAllAppealsFromApi()
    {
        try {
            $url = "{$this->baseUrl}/replacement/appeals";
            Log::info('Fetching all appeals from API', ['url' => $url]);
            
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $appealsData = $data['data'] ?? [];
                
                // Convert arrays to objects recursively for view compatibility
                return collect($appealsData)->map(function($appeal) {
                    return $this->arrayToObject($appeal);
                });
            }
            
            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('Failed to fetch appeals from API', [
                'url' => $url ?? "{$this->baseUrl}/replacement/appeals",
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to connect to API. Please ensure the API server is running on port 8001.');
        }
    }

    /**
     * Helper method to convert nested arrays to objects
     * This makes API responses compatible with views expecting object notation
     */
    private function arrayToObject($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $object = new \stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $object->$key = $this->arrayToObject($value);
            } else {
                $object->$key = $value;
            }
        }
        return $object;
    }

    /**
     * Get statistics from API
     */
    public function getStatisticsFromApi()
    {
        try {
            $url = "{$this->baseUrl}/replacement/statistics";
            Log::info('Fetching statistics from API', ['url' => $url]);
            
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $statsData = $data['data'] ?? [];
                
                return [
                    'total' => $statsData['total_appeals'] ?? 0,
                    'pending' => $statsData['pending_appeals'] ?? 0,
                    'approved' => $statsData['approved_appeals'] ?? 0,
                    'rejected' => $statsData['rejected_appeals'] ?? 0
                ];
            }
            
            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('Failed to fetch statistics from API', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to connect to API. Please ensure the API server is running on port 8001.');
        }
    }
}
