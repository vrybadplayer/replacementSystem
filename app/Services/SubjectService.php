<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SubjectService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    private function getInternalApiToken(): string
    {
        $secret = env('INTERNAL_API_SECRET', 'Abc0123456789');
        $timestamp = time();
        return base64_encode($secret . '|' . $timestamp . '|internal');
    }

    protected function fetchFromApi(string $endpoint)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->get($this->baseUrl . '/' . $endpoint);
        
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'];
        }
        return null;
    }


    public function fetchSubjectsFromApi()
    {
        return $this->fetchFromApi('subjects');
    }


    protected function fetchCourseSubjectsFromApi()
    {
        return $this->fetchFromApi('courseSubject');
    }


    public function getSubjectsForViewFromApi()
    {
        $subjects = $this->fetchSubjectsFromApi();
        $courseSubjects = $this->fetchCourseSubjectsFromApi();

        $subjectCoursesMap = [];
        foreach ($courseSubjects as $cs) {
            $subjectId = $cs['subjectId'];
            $courseSubjectId = $cs['courseSubjectId'];
            if ($subjectId && $courseSubjectId) {
                $codes = $this->getCourseSubjectCodes($courseSubjectId);
                $courseCode = $codes['courseCode'];
                if ($courseCode) {
                    $subjectCoursesMap[$subjectId] = array_unique(array_merge($subjectCoursesMap[$subjectId] ?? [], [$courseCode]));
                }
            }
        }

        return array_map(function ($subject) use ($subjectCoursesMap) {
            $courseCodes = $subjectCoursesMap[$subject['subjectId'] ?? ''] ?? [];
            $subject['courseCodes'] = $courseCodes;
            $subject['course'] = $courseCodes ? implode(', ', $courseCodes) : 'N/A';
            return $subject;
        }, $subjects);
    }


    public function fetchSubjectByIdFromApi(string $subjectId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->get($this->baseUrl . '/subjects/' . $subjectId);
        
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'];
        }
        return null;
    }

    public function createSubjectViaApi(array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->post($this->baseUrl . '/subjects', $data);
        
        return $response;
    }

    public function updateSubjectViaApi(string $subjectId, array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->put($this->baseUrl . '/subjects/' . $subjectId, $data);
        
        return $response;
    }

    public function getSubjectByCourseSubject($courseSubjectId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->get($this->baseUrl . '/subject/byCourseSubject/' . $courseSubjectId);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'];
        }

        return null;
    }

    protected function getCourseSubjectCodes($courseSubjectId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getInternalApiToken(),
            'X-Internal-Request' => 'true',
        ])->get($this->baseUrl . '/courseSubject/' . $courseSubjectId);
        
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'];
        }
        return null;
    }
}
