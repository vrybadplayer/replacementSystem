<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubjectService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';


    protected function fetchFromApi(string $endpoint)
    {
        $response = Http::get($this->baseUrl . '/' . $endpoint);
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
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
            $subjectId = $cs['subjectId'] ?? null;
            $courseSubjectId = $cs['courseSubjectId'] ?? null;
            if ($subjectId && $courseSubjectId) {
                $codes = $this->getCourseSubjectCodes($courseSubjectId);
                $courseCode = $codes['courseCode'] ?? null;
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
        $response = Http::timeout(5)->get("{$this->baseUrl}/subjects/{$subjectId}");
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }

        return null;
    }


    public function createOrUpdateSubject(array $data, ?string $subjectId = null)
    {
        $subjectId = $subjectId ?? $data['subjectId'] ?? null;
        if (!$subjectId) {
            throw new \InvalidArgumentException('Subject ID is required');
        }

        $response = Http::put($this->baseUrl . '/subjects/' . $subjectId, $data);
        if ($response->status() === 404) {
            $response = Http::post($this->baseUrl . '/subjects', $data);
        }
        if (!$response->successful()) {
            throw new \Exception($response->json()['message'] ?? 'Failed to create/update subject');
        }

        return $response->json()['data'] ?? $response->json();
    }

    public function getSubjectByCourseSubject($courseSubjectId)
    {
        $response = Http::get($this->baseUrl . '/subject/byCourseSubject/' . $courseSubjectId);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }

        return null;
    }

    protected function getCourseSubjectCodes($courseSubjectId)
    {
        $response = Http::get($this->baseUrl . '/courseSubject/' . $courseSubjectId);
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }
        return null;
    }
}
