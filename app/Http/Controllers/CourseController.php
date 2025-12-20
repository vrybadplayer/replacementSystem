<?php

namespace App\Http\Controllers;

use App\Facades\CourseSubjectManagementFacade;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function courseListView(Request $request)
    {
        $search = $request->query('search');
        $allCourses = CourseSubjectManagementFacade::getCoursesForViewFromApi();

        if ($search) {
            $allCourses = collect($allCourses)->filter(function ($course) use ($search) {
                $searchLower = Str::lower($search);
                $courseId = $course['courseId'];
                $courseCode = $course['courseCode'];
                $courseName = $course['courseName'];
                
                return Str::contains(Str::lower($courseId), $searchLower) ||
                       Str::contains(Str::lower($courseCode), $searchLower) ||
                       Str::contains(Str::lower($courseName), $searchLower);
            })->values()->all();
        }

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

        return view('admin.courseModule.listCourse', compact('courses', 'search'));
    }

    public function create()
    {
        return view('admin.courseModule.addCourse');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'courseId' => 'nullable|string|unique:course,courseId',
            'courseCode' => 'required|string|unique:course,courseCode',
            'courseName' => 'required|string|unique:course,courseName|different:courseCode',
            'faculty' => 'required|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        if (empty($validated['courseId'])) {
            $courses = CourseSubjectManagementFacade::fetchCoursesFromApi();
            $courseNumbers = [];
            foreach ($courses as $course) {
                $courseId = $course['courseId'];
                if (strpos($courseId, 'C') === 0 && is_numeric(substr($courseId, 1))) {
                    $courseNumbers[] = (int) substr($courseId, 1);
                }
            }
            
            $nextNumber = !empty($courseNumbers) ? max($courseNumbers) + 1 : 1;
            $validated['courseId'] = 'C' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }

        $response = CourseSubjectManagementFacade::createCourseViaApi($validated);
        
        if ($response->successful()) {
            return redirect()->route('admin.courses')
                ->with('success', 'Course created successfully!');
        }
        
        $errorMessage = $response->json()['message'];
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => $errorMessage]);
    }

    public function edit(string $courseId)
    {
        $course = CourseSubjectManagementFacade::getCourseByIdFromApi($courseId);
        return view('admin.courseModule.editCourse', compact('course'));
    }

    public function update(Request $request, string $courseId)
    {
        $validated = $request->validate([
            'courseCode' => 'required|string|unique:course,courseCode,' . $courseId . ',courseId',
            'courseName' => 'required|string|unique:course,courseName,' . $courseId . ',courseId|different:courseCode',
            'faculty' => 'required|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $response = CourseSubjectManagementFacade::updateCourseViaApi($courseId, $validated);
        
        if ($response->successful()) {
            return redirect()->route('admin.courses')
                ->with('success', 'Course updated successfully!');
        }
        
        $errorMessage = $response->json()['message'];
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => $errorMessage]);
    }


    public function apiGetAllCourses()
    {
        try {
            $data = Course::all();

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $data->count(),
            ], 200);
        } finally {
            DB::disconnect();
        }
    }

    public function apiCreateCourse(Request $request)
    {
        try {
            $course = Course::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $course,
            ], 201);
        } finally {
            DB::disconnect();
        }
    }

    public function apiGetCourse(string $courseId)
    {
        try {
            $course = Course::findOrFail($courseId);

            return response()->json([
                'success' => true,
                'data' => $course,
            ], 200);
        } finally {
            DB::disconnect();
        }
    }

    public function apiUpdateCourse(Request $request, string $courseId)
    {
        try {
            $course = Course::findOrFail($courseId);
            $course->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $course,
            ], 200);
        } finally {
            DB::disconnect();
        }
    }
}
