<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubjectService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';


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


    public function fetchSubjectsFromApi(): array
    {
        return $this->fetchFromApi('subjects');
    }


    protected function fetchCourseSubjectsFromApi(): array
    {
        return $this->fetchFromApi('courseSubject');
    }


    public function getSubjectsForViewFromApi(): array
    {
        $subjects = $this->fetchSubjectsFromApi();
        $courseSubjects = $this->fetchCourseSubjectsFromApi();

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

        return array_map(function ($subject) use ($subjectCoursesMap) {
            $subjectId = $subject['subjectId'] ?? '';
            $courseCodes = $subjectCoursesMap[$subjectId] ?? [];
            $subject['courseCodes'] = $courseCodes;
            $subject['course'] = !empty($courseCodes) ? implode(', ', $courseCodes) : 'N/A';
            return $subject;
        }, $subjects);
    }


    public function fetchSubjectByIdFromApi(string $subjectId): array
    {
        $response = Http::timeout(5)->get("{$this->baseUrl}/subjects/{$subjectId}");
        if ($response->status() === 404) {
            throw new \Exception('Subject not found');
        }
        if (!$response->successful()) {
            throw new \Exception('Subjects API failed: ' . $response->status());
        }
        $data = $response->json();
        return $data['data'] ?? (is_array($data) ? $data : []);
    }


    public function createOrUpdateSubject(array $data, ?string $subjectId = null): array
    {
        $subjectId = $subjectId ?? $data['subjectId'] ?? null;

        if (!$subjectId) {
            throw new \InvalidArgumentException('Subject ID is required');
        }

        $url = "{$this->baseUrl}/subjects/{$subjectId}";
        $response = Http::timeout(5)->put($url, $data);

        if ($response->status() === 404) {
            $response = Http::timeout(5)->post("{$this->baseUrl}/subjects", $data);
        }
        if (!$response->successful()) {
            throw new \Exception(
                $response->json()['message']
                    ?? 'Failed to create/update subject'
            );
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
}
