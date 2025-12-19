<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\CourseSubject;

class CourseSubjectService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    private function getInternalApiToken(): string
    {
        $secret = env('INTERNAL_API_SECRET', 'Abc0123456789');
        $timestamp = time();
        return base64_encode($secret . '|' . $timestamp . '|internal');
    }

    function fetchFromApi(string $endpoint)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->get($this->baseUrl . '/' . $endpoint);
        
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

    public function getCourseSubjectCodes($courseSubjectId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->get($this->baseUrl . '/courseSubject/' . $courseSubjectId);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }

        return null;
    }

    public function fetchCourseSubjectByIdFromApi(string $courseSubjectId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->get($this->baseUrl . '/courseSubject/' . $courseSubjectId);
        
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }
        return null;
    }


    public function createCourseSubjectViaApi(array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->post($this->baseUrl . '/courseSubject', $data);
        
        return $response;
    }

    public function updateCourseSubjectViaApi(string $courseSubjectId, array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->put($this->baseUrl . '/courseSubject/' . $courseSubjectId, $data);
        
        return $response;
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
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->get($this->baseUrl . '/courseSubjects/byCourse/' . $courseId);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? [];
        }

        return [];
    }
}
