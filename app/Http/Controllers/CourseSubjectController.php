<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Facades\CourseSubjectManagementFacade;
use App\Models\CourseSubject;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CourseSubjectController extends Controller
{

    protected $courseSubjectService;

    public function courseSubjectListView(Request $request)
    {
        $allCourseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi();

        $enrichedCourseSubjects = [];
        foreach ($allCourseSubjects as $courseSubject) {
            try {
                $courseSubjectId = $courseSubject['courseSubjectId'] ?? null;

                if (!$courseSubjectId) {
                    continue;
                }

                $codes = CourseSubjectManagementFacade::getCourseSubjectCodes($courseSubjectId);

                if ($codes) {
                    $courseSubject['course'] = [
                        'courseId' => $codes['courseId'],
                        'courseCode' => $codes['courseCode'],
                    ];

                    $courseSubject['subject'] = [
                        'subjectId' => $codes['subjectId'],
                        'subjectCode' => $codes['subjectCode'],
                    ];
                }

                $enrichedCourseSubjects[] = $courseSubject;
            } catch (\Exception $e) {
                $csId = $courseSubject['courseSubjectId'] ?? 'unknown';
                Log::warning("Failed to enrich course subject {$csId}: " . $e->getMessage());
            }
        }

        $allCourseSubjects = $enrichedCourseSubjects;

        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $total = count($allCourseSubjects);
        $items = array_slice($allCourseSubjects, ($currentPage - 1) * $perPage, $perPage);

        $courseSubjects = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.courseModule.listCourseSubject', compact('courseSubjects'));
    }

    public function create()
    {
        $courses = CourseSubjectManagementFacade::getCoursesForViewFromApi();
        $subjects = CourseSubjectManagementFacade::getSubjectsForViewFromApi();
        return view('admin.courseModule.addCourseSubject', compact('courses', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'courseSubjectId' => 'nullable|string|unique:course_subject,courseSubjectId',
            'courseId' => 'required|string|exists:course,courseId',
            'subjectId' => 'required|string|exists:subject,subjectId',
        ]);

        // Check if course and subject are active
        $course = CourseSubjectManagementFacade::getCourseByIdFromApi($validated['courseId']);
        $subject = CourseSubjectManagementFacade::getSubjectByIdFromApi($validated['subjectId']);
        
        if (!$course || ($course['status'] ?? '') !== 'active') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['courseId' => 'The selected course is not active.']);
        }
        
        if (!$subject || ($subject['status'] ?? '') !== 'active') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['subjectId' => 'The selected subject is not active.']);
        }

        // Ensure courseId and subjectId combination is unique
        $courseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi() ?? [];
        $existing = null;
        foreach ($courseSubjects as $courseSubject) {
            if (($courseSubject['courseId'] ?? '') === $validated['courseId'] && 
                ($courseSubject['subjectId'] ?? '') === $validated['subjectId']) {
                $existing = $courseSubject;
                break;
            }
        }
        
        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'This course-subject combination already exists.']);
        }

        if (empty($validated['courseSubjectId'])) {
            $courseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi() ?? [];
            $courseSubjectNumbers = [];
            foreach ($courseSubjects as $courseSubject) {
                $courseSubjectId = $courseSubject['courseSubjectId'] ?? '';
                if (strpos($courseSubjectId, 'CS') === 0 && is_numeric(substr($courseSubjectId, 2))) {
                    $courseSubjectNumbers[] = (int) substr($courseSubjectId, 2);
                }
            }
            
            $nextNumber = !empty($courseSubjectNumbers) ? max($courseSubjectNumbers) + 1 : 1;
            $validated['courseSubjectId'] = 'CS' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }

        $response = CourseSubjectManagementFacade::createCourseSubjectViaApi($validated);
        
        if ($response->successful()) {
            return redirect()->route('admin.courseSubjects')
                ->with('success', 'Course Subject created successfully!');
        }
        
        $errorMessage = $response->json()['message'] ?? 'Failed to create course subject';
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => $errorMessage]);
    }

    public function edit(string $courseSubjectId)
    {
        $courseSubject = CourseSubjectManagementFacade::getCourseSubjectByIdFromApi($courseSubjectId);
        $courses = CourseSubjectManagementFacade::getCoursesForViewFromApi();
        $subjects = CourseSubjectManagementFacade::getSubjectsForViewFromApi();
        return view('admin.courseModule.editCourseSubject', compact('courseSubject', 'courses', 'subjects'));
    }

    public function update(Request $request, string $courseSubjectId)
    {
        $validated = $request->validate([
            'courseId' => 'required|string|exists:course,courseId',
            'subjectId' => 'required|string|exists:subject,subjectId',
        ]);

        // Check if course and subject are active
        $course = CourseSubjectManagementFacade::getCourseByIdFromApi($validated['courseId']);
        $subject = CourseSubjectManagementFacade::getSubjectByIdFromApi($validated['subjectId']);
        
        if (!$course || ($course['status'] ?? '') !== 'active') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['courseId' => 'The selected course is not active.']);
        }
        
        if (!$subject || ($subject['status'] ?? '') !== 'active') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['subjectId' => 'The selected subject is not active.']);
        }

        // Ensure courseId and subjectId combination is unique (excluding current record)
        $courseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi() ?? [];
        $existing = null;
        foreach ($courseSubjects as $courseSubject) {
            if (($courseSubject['courseId'] ?? '') === $validated['courseId'] && 
                ($courseSubject['subjectId'] ?? '') === $validated['subjectId'] &&
                ($courseSubject['courseSubjectId'] ?? '') !== $courseSubjectId) {
                $existing = $courseSubject;
                break;
            }
        }
        
        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'This course-subject combination already exists.']);
        }

        $response = CourseSubjectManagementFacade::updateCourseSubjectViaApi($courseSubjectId, $validated);
        
        if ($response->successful()) {
            return redirect()->route('admin.courseSubjects')
                ->with('success', 'Course Subject updated successfully!');
        }
        
        $errorMessage = $response->json()['message'] ?? 'Failed to update course subject';
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => $errorMessage]);
    }

    /**
     * REST API
     */
    public function apiGetAllCourseSubjects()
    {
        try {
            $data = CourseSubject::all();

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $data->count(),
            ], 200);
        } finally {
            // Close database connection as soon as possible
            DB::disconnect();
        }
    }

    public function apiCreateCourseSubject(Request $request)
    {
        // Authorization check - only admins can create course subjects
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can create course subjects.',
            ], 403);
        }

        try {
            $courseSubject = CourseSubject::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $courseSubject,
            ], 201);
        } finally {
            // Close database connection as soon as possible
            DB::disconnect();
        }
    }

    public function apiUpdateCourseSubject(Request $request, string $id)
    {
        // Authorization check - only admins can update course subjects
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can update course subjects.',
            ], 403);
        }

        try {
            $courseSubject = CourseSubject::findOrFail($id);
            $courseSubject->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $courseSubject,
            ], 200);
        } finally {
            // Close database connection as soon as possible
            DB::disconnect();
        }
    }

    public function fetchCourseSubjectWithForeignKeys(Request $request)
    {
        $subjectId = $request->query('subjectId');
        $courseId = $request->query('courseId');

        $courseSubject = CourseSubjectManagementFacade::fetchCourseSubjectWithForeignKeys(
            $subjectId,
            $courseId
        );

        if (!$courseSubject) {
            return response()->json(null, 404);
        }

        return response()->json($courseSubject, 200);
    }

    // GET /api/courseSubjects/byCourse/{courseId}
    public function getCourseSubjectsByCourse($courseId)
    {
        // Direct database query in controller
        $courseSubjectIds = CourseSubject::where('courseId', $courseId)
            ->pluck('courseSubjectId')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $courseSubjectIds,
            'count' => count($courseSubjectIds)
        ]);
    }

    // GET /api/courseSubject/{courseSubjectId}
    public function getCourseSubject($courseSubjectId)
    {
        $courseSubject = CourseSubject::with(['course', 'subject'])
            ->where('courseSubjectId', $courseSubjectId)
            ->first();

        if (!$courseSubject) {
            return response()->json([
                'success' => false,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'courseSubjectId' => $courseSubject->courseSubjectId,
                'courseId' => $courseSubject->courseId,
                'courseCode' => $courseSubject->course->courseCode ?? '',
                'courseName' => $courseSubject->course->courseName ?? '',
                'subjectId' => $courseSubject->subjectId,
                'subjectCode' => $courseSubject->subject->subjectCode ?? '',
                'subjectName' => $courseSubject->subject->subjectName ?? '',
            ],
        ], 200);
    }
}
