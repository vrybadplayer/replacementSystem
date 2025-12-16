<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\CourseSubject;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseSubjectService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    /**
     * Generic method to fetch data from API
     * 
     * @param string $endpoint API endpoint (without base URL)
     * @return array API response data
     * @throws \Exception if API fails and throwOnFail is true
     */
    function fetchFromApi(string $endpoint): array
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
     * Fetch course subjects from API
     * 
     * @return array Raw API response data
     * @throws \Exception if API is unavailable
     */
    public function fetchCourseSubjectsFromApi(): array
    {
        return $this->fetchFromApi('courseSubject');
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

    public function fetchCourseSubjectWithForeignKeys($subjectId, $courseId)
    {
        $courseSubject = CourseSubject::where('subjectId', $subjectId)
            ->where('courseId', $courseId)
            ->first();
        
        if (!$courseSubject) {
            return null;
        }
        
        return [
            'courseSubjectId' => $courseSubject->courseSubjectId,
            'courseId' => $courseSubject->courseId,
            'subjectId' => $courseSubject->subjectId,
        ];
    }
}
