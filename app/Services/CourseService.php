<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Http;

class CourseService
{


    // ---------------------------- API TEST -----------------------------------------
    protected $baseUrl = 'http://127.0.0.1:8001/replacementSystem/public/api';


    public function fetchCourses()
    {
        $response = Http::get("{$this->baseUrl}/courses");

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    public function fetchCourses2()
    {
        $response = Http::get("{$this->baseUrl}/courses2");

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    public function fetchSubjects()
    {
        $response = Http::get("{$this->baseUrl}/subjects");

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    // -------------------------------------------------------------------------------


    /**
     * Get all courses
     */
    public function getAllCourses()
    {
        return Course::all();
    }

    /**
     * Get course by ID
     */
    public function getCourseById(string $courseId)
    {
        return Course::find($courseId);
    }

    /**
     * Create or update a course
     */
    public function createOrUpdateCourse(array $data, string $courseId = null)
    {
        return Course::updateOrCreate(
            ['courseId' => $courseId ?? $data['courseId']],
            $data
        );
    }

    /**
     * Create a new course
     */
    public function createCourse(array $data)
    {
        return Course::create($data);
    }

    /**
     * Update a course
     */
    public function updateCourse(string $courseId, array $data)
    {
        $course = Course::findOrFail($courseId);
        $course->update($data);
        return $course;
    }

    /**
     * Delete a course
     */
    public function deleteCourse(string $courseId)
    {
        $course = Course::findOrFail($courseId);
        return $course->delete();
    }

    /**
     * Get courses formatted for views
     */
    public function getCoursesForView()
    {
        return Course::all()->map(function ($course) {
            return [
                'id' => $course->courseId,
                'code' => $course->courseCode,
                'name' => $course->courseName,
                'faculty' => $course->faculty,
                'status' => $course->status,
            ];
        })->toArray();
    }
}

