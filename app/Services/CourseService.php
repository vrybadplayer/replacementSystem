<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;

class CourseService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    /**
     * Create a token for internal API calls
     */
    private function getInternalApiToken(): string
    {
        // Generate token with secret, timestamp, and type
        $secret = env('INTERNAL_API_SECRET', 'your-secret-key-change-this');
        $timestamp = time();
        $payload = [
            'type' => 'internal',
            'timestamp' => $timestamp,
        ];
        
        // Create token: base64(secret|timestamp|type)
        return base64_encode($secret . '|' . $timestamp . '|internal');
    }

    
    public function fetchCoursesFromApi()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->get($this->baseUrl . '/courses');
        
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }
        return null;
    }


    public function getCoursesForViewFromApi()
    {
        return $this->fetchCoursesFromApi();
    }


    public function fetchCourseByIdFromApi(string $courseId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->get($this->baseUrl . '/courses/' . $courseId);
        
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }
        return null;
    }

    public function createCourseViaApi(array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->post($this->baseUrl . '/courses', $data);
        
        return $response;
    }

    public function updateCourseViaApi(string $courseId, array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->put($this->baseUrl . '/courses/' . $courseId, $data);
        
        return $response;
    }
}
