<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;

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
     * Create or update a course in the database and sync to external API
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

        // Save to local database
        $course = Course::updateOrCreate(
            ['courseId' => $courseId],
            $data
        );

        // Sync to external API
        $this->syncCourseToApi($data);

        return $course;
    }

    /**
     * Sync course to external API
     * Silently fails if API is unavailable - local save is primary
     * 
     * @param array $data Course data
     * @return void
     */
    protected function syncCourseToApi(array $data): void
    {
        try {
            $url = "{$this->baseUrl}/courses";
            $response = Http::timeout(3)->post($url, $data);

            if (!$response->successful()) {
                Log::warning('Failed to sync course to external API', [
                    'courseId' => $data['courseId'] ?? null,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (ConnectionException $e) {
            // API is unavailable - log and continue
            Log::warning('External API unavailable when syncing course', [
                'courseId' => $data['courseId'] ?? null,
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            // Catch any other exceptions
            Log::warning('Error syncing course to external API', [
                'courseId' => $data['courseId'] ?? null,
                'error' => $e->getMessage()
            ]);
        }
        // Don't throw - local save succeeded, API sync is secondary
    }
}
