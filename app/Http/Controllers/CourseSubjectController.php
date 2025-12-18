<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Facades\CourseSubjectManagementFacade;
use App\Models\CourseSubject;
use App\Services\CourseSubjectService;
use Illuminate\Pagination\LengthAwarePaginator;

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
            'courseSubjectId' => 'required|string',
            'courseId' => 'required|string',
            'subjectId' => 'required|string',
        ]);
        CourseSubjectManagementFacade::createOrUpdateCourseSubject($validated);
        return redirect()->route('admin.courseSubjects')
            ->with('success', 'Course Subject created successfully!');
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
            'courseId' => 'required|string',
            'subjectId' => 'required|string',
        ]);
        $validated['courseSubjectId'] = $courseSubjectId;
        CourseSubjectManagementFacade::createOrUpdateCourseSubject($validated, $courseSubjectId);
        return redirect()->route('admin.courseSubjects')
            ->with('success', 'Course Subject updated successfully!');
    }

    /**
     * REST API
     */
    public function index()
    {
        $data = CourseSubject::all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'count' => $data->count(),
        ], 200);
    }

    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'courseSubjectId' => 'required|string|unique:course_subject,courseSubjectId',
            'courseId' => 'required|string|exists:course,courseId',
            'subjectId' => 'required|string|exists:subject,subjectId',
        ]);

        $courseSubject = CourseSubject::create($validated);

        return response()->json([
            'success' => true,
            'data' => $courseSubject,
        ], 201);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $validated = $request->validate([
            'courseId' => 'nullable|string|exists:course,courseId',
            'subjectId' => 'nullable|string|exists:subject,subjectId',
        ]);

        $courseSubject = CourseSubject::findOrFail($id);
        $courseSubject->update($validated);

        return response()->json([
            'success' => true,
            'data' => $courseSubject,
        ], 200);
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

        // Return just the data
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

    // GET /api/courseSubject/{courseSubjectId}/codes
    public function getCourseSubjectCodes($courseSubjectId)
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
