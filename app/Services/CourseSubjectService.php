<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\CourseSubject;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseSubjectService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    
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


    public function fetchCourseSubjectsFromApi(): array
    {
        return $this->fetchFromApi('courseSubject');
    }


    public function fetchCourseSubjectByIdFromApi(string $courseSubjectId): array
    {
        $response = Http::timeout(5)->get("{$this->baseUrl}/courseSubject/{$courseSubjectId}");
        if ($response->status() === 404) {
            throw new \Exception('Course Subject not found');
        }
        if (!$response->successful()) {
            throw new \Exception('Course Subject API failed: ' . $response->status());
        }
        $data = $response->json();
        return $data['data'] ?? (is_array($data) ? $data : []);
    }


    public function createOrUpdateCourseSubject(array $data, ?string $courseSubjectId = null): array
    {
        $courseSubjectId = $courseSubjectId ?? $data['courseSubjectId'] ?? null;

        if (!$courseSubjectId) {
            throw new \InvalidArgumentException('Course Subject ID is required');
        }

        $url = "{$this->baseUrl}/courseSubject/{$courseSubjectId}";
        $response = Http::timeout(5)->put($url, $data);

        if ($response->status() === 404) {
            $response = Http::timeout(5)->post("{$this->baseUrl}/courseSubject", $data);
        }
        if (!$response->successful()) {
            throw new \Exception(
                $response->json()['message']
                    ?? 'Failed to create/update course subject'
            );
        }

        return $response->json()['data'] ?? $response->json();
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
