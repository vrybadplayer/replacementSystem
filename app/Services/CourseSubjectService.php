<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\CourseSubject;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseSubjectService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';


    function fetchFromApi(string $endpoint)
    {
        $response = Http::get($this->baseUrl . '/' . $endpoint);
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }
        return null;
    }


    public function fetchCourseSubjectsFromApi()
    {
        return $this->fetchFromApi('courseSubject');
    }


    public function fetchCourseSubjectByIdFromApi(string $courseSubjectId)
    {
        $response = Http::get($this->baseUrl . '/courseSubject/' . $courseSubjectId);
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }
        return null;
    }


    public function createOrUpdateCourseSubject(array $data, ?string $courseSubjectId = null)
    {
        $courseSubjectId = $courseSubjectId ?? $data['courseSubjectId'] ?? null;
        if (!$courseSubjectId) {
            throw new \InvalidArgumentException('Course Subject ID is required');
        }

        $response = Http::put($this->baseUrl . '/courseSubject/' . $courseSubjectId, $data);
        if ($response->status() === 404) {
            $response = Http::post($this->baseUrl . '/courseSubject', $data);
        }
        if (!$response->successful()) {
            throw new \Exception($response->json()['message'] ?? 'Failed to create/update course subject');
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

    public function getCourseSubjectsByCourse($courseId)
    {
        $response = Http::get($this->baseUrl . '/courseSubjects/byCourse/' . $courseId);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? [];
        }

        return [];
    }

    public function getCourseSubjectCodes($courseSubjectId)
    {
        $response = Http::get($this->baseUrl . '/courseSubject/' . $courseSubjectId);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }

        return null;
    }
}
