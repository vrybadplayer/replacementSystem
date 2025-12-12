<?php

namespace App\Services;

use App\Models\Subject;

class SubjectService
{
    /**
     * Get all subjects
     */
    public function getAllSubjects()
    {
        return Subject::all();
    }

    /**
     * Get subject by ID
     */
    public function getSubjectById(string $subjectId)
    {
        return Subject::find($subjectId);
    }

    /**
     * Create or update a subject
     */
    public function createOrUpdateSubject(array $data, string $subjectId = null)
    {
        return Subject::updateOrCreate(
            ['subjectId' => $subjectId ?? $data['subjectId']],
            $data
        );
    }

    /**
     * Create a new subject
     */
    public function createSubject(array $data)
    {
        return Subject::create($data);
    }

    /**
     * Update a subject
     */
    public function updateSubject(string $subjectId, array $data)
    {
        $subject = Subject::findOrFail($subjectId);
        $subject->update($data);
        return $subject;
    }

    /**
     * Delete a subject
     */
    public function deleteSubject(string $subjectId)
    {
        $subject = Subject::findOrFail($subjectId);
        return $subject->delete();
    }

    /**
     * Get subjects formatted for views
     */
    public function getSubjectsForView()
    {
        return Subject::with(['courses' => function($query) {
            $query->select('course.courseId', 'course.courseCode');
        }])->get()->map(function ($subject) {
            // Get the first course code from the relationship
            $courseCode = $subject->courses->first() ? $subject->courses->first()->courseCode : 'N/A';
            
            return [
                'id' => $subject->subjectId,
                'code' => $subject->subjectCode,
                'name' => $subject->subjectName,
                'credits' => $subject->creditHours,
                'course' => $courseCode,
                'lecturer' => null,
                'type' => $subject->category,
                'status' => $subject->status,
            ];
        })->toArray();
    }
}

