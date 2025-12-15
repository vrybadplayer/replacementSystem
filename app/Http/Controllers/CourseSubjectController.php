<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Facades\CourseSubjectManagementFacade;
use App\Models\CourseSubject;

class CourseSubjectController extends Controller
{
    /**
     * Web View: GET /admin/courseSubjects
     * Return view with course subjects list FROM EXTERNAL API
     */
    public function courseSubjectListView()
    {
        $courseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi();
        return view('admin.courseModule.listCourseSubject', compact('courseSubjects'));
    }

    /**
     * REST API: GET /api/courseSubject
     * Get all course subjects data FROM DATABASE directly (to avoid circular API calls)
     * This endpoint is called by the web view's API fetch
     */
    public function index()
    {
        // Get all course subjects FROM DATABASE directly (bypassing services to avoid circular calls)
        $courseSubjects = CourseSubject::with(['course', 'subject'])->get();

        // Map to API response format
        $courseSubjectsData = $courseSubjects->map(function ($courseSubject) {
            return [
                'courseSubjectId' => $courseSubject->courseSubjectId,
                'courseId' => $courseSubject->courseId,
                'subjectId' => $courseSubject->subjectId,
                'course' => $courseSubject->course ? [
                    'courseId' => $courseSubject->course->courseId,
                    'courseName' => $courseSubject->course->courseName ?? '',
                ] : null,
                'subject' => $courseSubject->subject ? [
                    'subjectId' => $courseSubject->subject->subjectId,
                    'subjectCode' => $courseSubject->subject->subjectCode ?? '',
                    'subjectName' => $courseSubject->subject->subjectName ?? '',
                ] : null,
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => $courseSubjectsData,
            'count' => count($courseSubjectsData)
        ], 200);
    }

    /**
     * REST API: POST /api/courseSubject
     * Create a new course subject
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'courseSubjectId' => 'required|string|unique:course_subject,courseSubjectId',
            'courseId' => 'required|string|exists:course,courseId',
            'subjectId' => 'required|string|exists:subject,subjectId',
        ]);

        try {
            $courseSubject = CourseSubjectManagementFacade::createCourseSubject($validated);

            return response()->json([
                'success' => true,
                'message' => 'Course subject created successfully',
                'data' => [
                    'courseSubjectId' => $courseSubject->courseSubjectId,
                    'courseId' => $courseSubject->courseId,
                    'subjectId' => $courseSubject->subjectId,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create course subject', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create course subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * REST API: PUT/PATCH /api/courseSubject/{id}
     * Update a course subject
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'courseId' => 'nullable|string|exists:course,courseId',
            'subjectId' => 'nullable|string|exists:subject,subjectId',
        ]);

        try {
            $courseSubject = CourseSubjectManagementFacade::updateCourseSubject($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Course subject updated successfully',
                'data' => [
                    'courseSubjectId' => $courseSubject->courseSubjectId,
                    'courseId' => $courseSubject->courseId,
                    'subjectId' => $courseSubject->subjectId,
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Course subject not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to update course subject', [
                'error' => $e->getMessage(),
                'id' => $id,
                'data' => $validated
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update course subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
