<?php

namespace App\Services;

use App\Models\Subject;

class SubjectService
{

    protected $baseUrl = 'http://127.0.0.1:8001/api';

    /**
     * Get all subjects with courses relationship loaded
     */
    public function getAllSubjects()
    {
        return Subject::with(['courses'])->get();
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
        return Subject::with(['courses'])->get()->map(function ($subject) {
            // Get all courses from the course table via the relationship
            $courses = $subject->courses;
            
            // Display course codes, comma-separated if multiple courses
            $courseCodes = $courses->isNotEmpty() 
                ? $courses->pluck('courseCode')->join(', ')
                : 'N/A';
            
            return [
                'id' => $subject->subjectId,
                'code' => $subject->subjectCode ?? '',
                'name' => $subject->subjectName ?? '',
                'credits' => $subject->creditHours ?? 0,
                'course' => $courseCodes,
                'lecturer' => null,
                'type' => $subject->category ?? '',
                'status' => $subject->status ?? 'active',
            ];
        })->toArray();
    }
}

