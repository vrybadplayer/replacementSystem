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
        $response = Http::timeout(5)->get("{$this->baseUrl}/courses");
        if (!$response->successful()) {
            throw new \Exception('Courses API failed: ' . $response->status());
        }
        $data = $response->json();
        return $data['data'] ?? (is_array($data) ? $data : []);
    }

    /**
     * Fetch from API for views
     * ONLY fetches from external API (no database fallback)
     * 
     * @return array Courses data from API
     * @throws \Exception if API is unavailable
     */
    public function getCoursesForViewFromApi(): array
    {
        return $this->fetchCoursesFromApi();
    }

    /**
     * Create or update a course in the database
     * 
     * @param array $data Course data
     * @param string|null $courseId Course ID (if null, will use data['courseId'])
     * @return Course
     */
    public function createOrUpdateCourse(array $data, ?string $courseId = null): Course
    {
        $courseId = $courseId ?? $data['courseId'] ?? null;

        if (!$courseId) {
            throw new \InvalidArgumentException('Course ID is required');
        }

        return Course::updateOrCreate(
            ['courseId' => $courseId],
            $data
        );
    }
}
