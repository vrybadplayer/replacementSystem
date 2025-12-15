<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\CourseSubject;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseSubjectService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    /**
     * Get all course subjects
     * (Kept your API logic here for consistency, though Eloquent is usually preferred for internal services)
     */
    public function getAllCourseSubjects()
    {
        // Try API first if preferred
        try {
            $response = Http::get("{$this->baseUrl}/courseSubject");
            
            if ($response->successful()){
                return $response->json();
            }
        } catch (\Exception $e) {
            // Fallback to DB if API fails (good for robustness)
            return CourseSubject::all();
        }
        
        return [];
    }

    /**
     * Get single course subject by ID
     */
    public function getCourseSubjectById(string $courseSubjectId)
    {
        return CourseSubject::find($courseSubjectId);
    }

    /**
     * Create or Update (CRITICAL FOR SEEDER)
     * This uses Eloquent directly to ensure the Seed runs successfully.
     */
    public function createOrUpdateCourseSubject(array $data, string $courseSubjectId = null)
    {
        // If ID is provided as argument, use it for the lookup
        // If not, try to find it in the data array
        $id = $courseSubjectId ?? $data['courseSubjectId'] ?? null;

        if ($id) {
            return CourseSubject::updateOrCreate(
                ['courseSubjectId' => $id], // Search criteria
                $data                       // Data to fill
            );
        }

        // If no ID exists, create new
        return $this->createCourseSubject($data);
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
        $record = CourseSubject::findOrFail($courseSubjectId);
        $record->update($data);
        return $record;
    }

    /**
     * Delete a course subject
     */
    public function deleteCourseSubject(string $courseSubjectId)
    {
        $record = CourseSubject::find($courseSubjectId);
        
        if ($record) {
            return $record->delete();
        }
        
        return false;
    }
}