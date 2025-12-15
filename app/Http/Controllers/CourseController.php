<?php

namespace App\Http\Controllers;

use App\Facades\CourseSubjectManagementFacade;
use App\Models\Course;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    /**
     * Web View: GET /admin/courses
     * Return view with courses list FROM EXTERNAL API
     */
    public function courseListView()
    {
        $courses = CourseSubjectManagementFacade::getCoursesForViewFromApi();
        return view('admin.courseModule.listCourse', compact('courses'));
    }

    /**
     * REST API: GET /api/courses
     * Get all courses data FROM DATABASE directly (to avoid circular API calls)
     * This endpoint is called by the web view's API fetch
     */
    public function index()
    {
        // Get all courses FROM DATABASE directly (bypassing services to avoid circular calls)
        $courses = Course::all();

        // Map to API response format
        $coursesData = $courses->map(function ($course) {
            return [
                'courseId' => $course->courseId,
                'courseCode' => $course->courseCode,
                'courseName' => $course->courseName ?? '',
                'faculty' => $course->faculty ?? '',
                'status' => $course->status ?? 'active',
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => $coursesData,
            'count' => count($coursesData)
        ], 200);
    }
}
