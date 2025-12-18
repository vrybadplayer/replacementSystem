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
        $response = Http::timeout(5)->get("{$this->baseUrl}/courses");
        if (!$response->successful()) {
            throw new \Exception('Courses API failed: ' . $response->status());
        }
        $data = $response->json();
        return $data['data'] ?? (is_array($data) ? $data : []);
    }


    public function getCoursesForViewFromApi()
    {
        return $this->fetchCoursesFromApi();
    }


    public function fetchCourseByIdFromApi(string $courseId)
    {
        $response = Http::timeout(5)->get("{$this->baseUrl}/courses/{$courseId}");
        if ($response->status() === 404) {
            throw new \Exception('Course not found');
        }
        if (!$response->successful()) {
            throw new \Exception('Courses API failed: ' . $response->status());
        }
        $data = $response->json();
        return $data['data'] ?? (is_array($data) ? $data : []);
    }


    public function createOrUpdateCourse(array $data, ?string $courseId = null)
    {
        $courseId = $courseId ?? $data['courseId'] ?? null;

        if (!$courseId) {
            throw new \InvalidArgumentException('Course ID is required');
        }

        $url = "{$this->baseUrl}/courses/{$courseId}";
        $response = Http::timeout(5)->put($url, $data);

        if ($response->status() === 404) {
            $response = Http::timeout(5)->post("{$this->baseUrl}/courses", $data);
        }
        if (!$response->successful()) {
            throw new \Exception(
                $response->json()['message']
                    ?? 'Failed to create/update course'
            );
        }

        return $response->json()['data'] ?? $response->json();
    }
}
