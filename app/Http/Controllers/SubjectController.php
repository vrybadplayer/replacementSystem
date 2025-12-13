<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Facades\CourseManagementFacade;

class SubjectController extends Controller
{
    /**
     * Web View: GET /admin/subjects
     * Return view with subjects list
     */
    public function subjectListView()
    {
        // Single point to get subjects with course information
        // Can optionally pass filters: ['status' => 'active', 'category' => 'Core', 'search' => 'Database']
        $subjects = CourseManagementFacade::getSubjectsWithCoursesForView();

        // Return view with processed data
        return view('subject.listSubject', compact('subjects'));
    }

    /**
     * REST API: GET /api/subjects
     * Get all subjects data
     */
    public function index()
    {
        // Get all subjects
        $subjects = CourseManagementFacade::getAllSubjects();

        // Map to API response format
        $subjectsData = $subjects->map(function ($subject) {
            return [
                'subjectId' => $subject->subjectId,
                'subjectCode' => $subject->subjectCode ?? '',
                'subjectName' => $subject->subjectName ?? '',
                'creditHours' => $subject->creditHours ?? 0,
                'category' => $subject->category ?? '',
                'status' => $subject->status ?? 'active',
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => $subjectsData,
            'count' => count($subjectsData)
        ], 200);
    }

    /**
     * REST API: GET /api/subjects/{id}
     * Get a single subject by ID
     */
    public function show(string $id)
    {
        $subject = CourseManagementFacade::getSubjectById($id);
        
        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        }

        // Prepare course codes
        $subject = CourseManagementFacade::prepareCourseCodes(collect([$subject]))->first();

        return response()->json([
            'success' => true,
            'data' => [
                'subjectId' => $subject->subjectId,
                'subjectCode' => $subject->subjectCode ?? '',
                'subjectName' => $subject->subjectName ?? '',
                'creditHours' => $subject->creditHours ?? 0,
                'category' => $subject->category ?? '',
                'status' => $subject->status ?? 'active',
                'courseCode' => $subject->courseCodes ?? 'N/A',
            ]
        ], 200);
    }

    /**
     * REST API: POST /api/subjects
     * Create a new subject
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subjectId' => 'required|string|unique:subject,subjectId',
            'subjectCode' => 'nullable|string',
            'subjectName' => 'required|string',
            'creditHours' => 'nullable|integer',
            'category' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        try {
            $subject = CourseManagementFacade::createSubject($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Subject created successfully',
                'data' => [
                    'subjectId' => $subject->subjectId,
                    'subjectCode' => $subject->subjectCode ?? '',
                    'subjectName' => $subject->subjectName ?? '',
                    'creditHours' => $subject->creditHours ?? 0,
                    'category' => $subject->category ?? '',
                    'status' => $subject->status ?? 'active',
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * REST API: PUT/PATCH /api/subjects/{id}
     * Update a subject
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'subjectCode' => 'nullable|string',
            'subjectName' => 'nullable|string',
            'creditHours' => 'nullable|integer',
            'category' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        try {
            $subject = CourseManagementFacade::updateSubject($id, $validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Subject updated successfully',
                'data' => [
                    'subjectId' => $subject->subjectId,
                    'subjectCode' => $subject->subjectCode ?? '',
                    'subjectName' => $subject->subjectName ?? '',
                    'creditHours' => $subject->creditHours ?? 0,
                    'category' => $subject->category ?? '',
                    'status' => $subject->status ?? 'active',
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * REST API: DELETE /api/subjects/{id}
     * Delete a subject
     */
    public function destroy(string $id)
    {
        try {
            $deleted = CourseManagementFacade::deleteSubject($id);
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject deleted successfully'
                ], 200);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subject'
            ], 500);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

