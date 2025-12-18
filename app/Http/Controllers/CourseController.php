<?php

namespace App\Http\Controllers;

use App\Facades\CourseSubjectManagementFacade;
use App\Models\Course;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class CourseController extends Controller
{
    public function courseListView(Request $request)
    {
        $allCourses = CourseSubjectManagementFacade::getCoursesForViewFromApi();

        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $total = count($allCourses);
        $items = array_slice($allCourses, ($currentPage - 1) * $perPage, $perPage);

        $courses = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.courseModule.listCourse', compact('courses'));
    }

    public function create()
    {
        return view('admin.courseModule.addCourse');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'courseId' => 'required|string',
            'courseCode' => 'required|string',
            'courseName' => 'required|string',
            'faculty' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        CourseSubjectManagementFacade::createOrUpdateCourse($validated);

        return redirect()->route('admin.courses')
            ->with('success', 'Course created successfully!');
    }

    public function edit(string $courseId)
    {
        $course = CourseSubjectManagementFacade::getCourseByIdFromApi($courseId);
        return view('admin.courseModule.editCourse', compact('course'));
    }

    public function update(Request $request, string $courseId)
    {
        $validated = $request->validate([
            'courseCode' => 'required|string',
            'courseName' => 'required|string',
            'faculty' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        // Add courseId to validated data for the API call
        $validated['courseId'] = $courseId;

        CourseSubjectManagementFacade::createOrUpdateCourse($validated, $courseId);

        return redirect()->route('admin.courses')
            ->with('success', 'Course updated successfully!');
    }


    /**
     * REST API
     */

    public function index()
    {
        $data = Course::all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'count' => $data->count(),
        ], 200);
    }

    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'courseId' => 'required|string|unique:course,courseId',
            'courseCode' => 'required|string',
            'courseName' => 'required|string',
            'faculty' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $course = Course::create($validated);

        return response()->json([
            'success' => true,
            'data' => $course,
        ], 201);
    }

    public function apiShow(string $courseId)
    {
        $course = Course::findOrFail($courseId);

        return response()->json([
            'success' => true,
            'data' => $course,
        ], 200);
    }

    public function apiUpdate(Request $request, string $courseId)
    {
        $validated = $request->validate([
            'courseCode' => 'nullable|string',
            'courseName' => 'nullable|string',
            'faculty' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $course = Course::findOrFail($courseId);
        $course->update($validated);

        return response()->json([
            'success' => true,
            'data' => $course,
        ], 200);
    }
}
