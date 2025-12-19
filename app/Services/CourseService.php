<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CourseService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    private function getInternalApiToken(): string
    {
        $secret = env('INTERNAL_API_SECRET', 'Abc0123456789');
        $timestamp = time();
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
            return $data['data'];
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
            return $data['data'];
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
