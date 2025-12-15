<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Facades\CourseSubjectManagementFacade;
use App\Models\Subject;

class SubjectController extends Controller
{
    
    /**
     * REST API: GET /api/subjects
     * Get all subjects data FROM DATABASE directly (to avoid circular API calls)
     * This endpoint is called by the web view's API fetch
     */
    public function index()
    {
        // Get all subjects FROM DATABASE directly (bypassing services to avoid circular calls)
        $subjects = Subject::with(['courses'])->get();
        
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
     * Web View: GET /admin/subjects
     * Return view with subjects list FROM EXTERNAL API
     */
    public function subjectListView()
    {
        try {
            // Fetch subjects FROM EXTERNAL API (port 8001)
            // Requires separate API server running on port 8001
            $subjects = CourseSubjectManagementFacade::getSubjectsForViewFromApi();

            // Return view with processed data
            return view('admin.subject.listSubject', compact('subjects'));
        } catch (\Exception $e) {
            // If API fails, show error message
            Log::error('Failed to fetch subjects from API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('admin.subject.listSubject', [
                'subjects' => [],
                'error' => 'Failed to load subjects from API: ' . $e->getMessage()
            ]);
        }
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
            $subject = CourseSubjectManagementFacade::createSubject($validated);
            
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
            $subject = CourseSubjectManagementFacade::updateSubject($id, $validated);
            
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
}

