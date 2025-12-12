<?php

namespace App\Services;

use App\Models\Course;

class CourseService
{
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
                'credits' => null,
                'department' => $course->faculty,
                'status' => $course->status,
            ];
        })->toArray();
    }
}

