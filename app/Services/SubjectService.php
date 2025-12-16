<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubjectService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    /**
     * Generic method to fetch data from API
     * 
     * @param string $endpoint API endpoint (without base URL)
     * @return array API response data
     * @throws \Exception if API fails and throwOnFail is true
     */
    protected function fetchFromApi(string $endpoint): array
    {
        $url = "{$this->baseUrl}/{$endpoint}";

        $response = Http::timeout(5)->get($url);

        if (!$response->successful()) {
            throw new \Exception("API failed: {$endpoint}");
        }

        $data = $response->json();
        return $data['data'] ?? (is_array($data) ? $data : []);
    }

    /**
     * Fetch subjects from API
     * 
     * @return array Raw API response data
     * @throws \Exception if API is unavailable
     */
    public function fetchSubjectsFromApi(): array
    {
        return $this->fetchFromApi('subjects');
    }

    /**
     * Fetch course subjects from API to get course data
     * @return array Course subjects data
     */
    protected function fetchCourseSubjectsFromApi(): array
    {
        return $this->fetchFromApi('courseSubject');
    }

    /**
     * Fetch from API for views with course information
     * ONLY fetches from external API (no database fallback)
     * 
     * @return array Subjects data from API with course codes added
     * @throws \Exception if API is unavailable
     */
    public function getSubjectsForViewFromApi(): array
    {
        $subjects = $this->fetchSubjectsFromApi();
        $courseSubjects = $this->fetchCourseSubjectsFromApi();

        // Create a map of subjectId to course codes
        $subjectCoursesMap = [];
        foreach ($courseSubjects as $courseSubject) {
            $subjectId = $courseSubject['subjectId'] ?? null;
            $courseCode = $courseSubject['course']['courseCode'] ?? $courseSubject['courseCode'] ?? null;

            if ($subjectId && $courseCode) {
                if (!isset($subjectCoursesMap[$subjectId])) {
                    $subjectCoursesMap[$subjectId] = [];
                }
                if (!in_array($courseCode, $subjectCoursesMap[$subjectId])) {
                    $subjectCoursesMap[$subjectId][] = $courseCode;
                }
            }
        }

        // Add course codes to each subject
        return array_map(function ($subject) use ($subjectCoursesMap) {
            $subjectId = $subject['subjectId'] ?? '';
            $courseCodes = $subjectCoursesMap[$subjectId] ?? [];
            $subject['courseCodes'] = $courseCodes;
            $subject['course'] = !empty($courseCodes) ? implode(', ', $courseCodes) : 'N/A';
            return $subject;
        }, $subjects);
    }

    /**
     * Create or update a subject in the database
     * 
     * @param array $data Subject data
     * @param string|null $subjectId Subject ID (if null, will use data['subjectId'])
     * @return Subject
     */
    public function createOrUpdateSubject(array $data, ?string $subjectId = null): Subject
    {
        $subjectId = $subjectId ?? $data['subjectId'] ?? null;

        if (!$subjectId) {
            throw new \InvalidArgumentException('Subject ID is required');
        }

        return Subject::updateOrCreate(
            ['subjectId' => $subjectId],
            $data
        );
    }
}
