<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;

class CourseService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    
    public function fetchCoursesFromApi()
    {
        $response = Http::get($this->baseUrl . '/courses');
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
        $response = Http::get($this->baseUrl . '/courses/' . $courseId);
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }
        return null;
    }


    public function createOrUpdateCourse(array $data, ?string $courseId = null)
    {
        $courseId = $courseId ?? $data['courseId'] ?? null;
        if (!$courseId) {
            throw new \InvalidArgumentException('Course ID is required');
        }

        $response = Http::put($this->baseUrl . '/courses/' . $courseId, $data);
        if ($response->status() === 404) {
            $response = Http::post($this->baseUrl . '/courses', $data);
        }
        if (!$response->successful()) {
            throw new \Exception($response->json()['message'] ?? 'Failed to create/update course');
        }

        return $response->json()['data'] ?? $response->json();
    }
}
