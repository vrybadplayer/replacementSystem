<?php

namespace App\Services;

use App\Models\CourseSubject;

class CourseSubjectService
{
    /**
     * Get all course subjects
     */
    public function getAllCourseSubjects()
    {
        return CourseSubject::all();
    }

    /**
     * Get course subject by ID
     */
    public function getCourseSubjectById(string $courseSubjectId)
    {
        return CourseSubject::find($courseSubjectId);
    }

    /**
     * Create or update a course subject
     */
    public function createOrUpdateCourseSubject(array $data, string $courseSubjectId = null)
    {
        return CourseSubject::updateOrCreate(
            ['courseSubjectId' => $courseSubjectId ?? $data['courseSubjectId']],
            $data
        );
    }

    /**
     * Create a new course subject
     */
    public function createCourseSubject(array $data)
    {
        return CourseSubject::create($data);
    }

    /**
     * Update a course subject
     */
    public function updateCourseSubject(string $courseSubjectId, array $data)
    {
        $courseSubject = CourseSubject::findOrFail($courseSubjectId);
        $courseSubject->update($data);
        return $courseSubject;
    }

    /**
     * Delete a course subject
     */
    public function deleteCourseSubject(string $courseSubjectId)
    {
        $courseSubject = CourseSubject::findOrFail($courseSubjectId);
        return $courseSubject->delete();
    }
}

