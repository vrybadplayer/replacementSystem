<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Facades\CourseSubjectManagementFacade;
use App\Models\CourseSubject;
use App\Services\CourseSubjectService;

class CourseSubjectController extends Controller
{

    protected $courseSubjectService;

    public function __construct(CourseSubjectService $courseSubjectService)
    {
        $this->courseSubjectService = $courseSubjectService;
    }

    public function courseSubjectListView()
    {
        $courseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi();
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
        $courseSubjects = CourseSubject::with(['course', 'subject'])->get();

        $courseSubjectsData = $courseSubjects->map(function ($courseSubject) {
            return [
                'courseSubjectId' => $courseSubject->courseSubjectId,
                'courseId' => $courseSubject->courseId,
                'subjectId' => $courseSubject->subjectId,
                'course' => $courseSubject->course ? [
                    'courseId' => $courseSubject->course->courseId,
                    'courseCode' => $courseSubject->course->courseCode ?? '',
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

    public function apiShow(string $courseSubjectId)
    {
        $courseSubject = CourseSubject::with(['course', 'subject'])->findOrFail($courseSubjectId);

        return response()->json([
            'success' => true,
            'data' => [
                'courseSubjectId' => $courseSubject->courseSubjectId,
                'courseId' => $courseSubject->courseId,
                'subjectId' => $courseSubject->subjectId,
                'course' => $courseSubject->course ? [
                    'courseId' => $courseSubject->course->courseId,
                    'courseCode' => $courseSubject->course->courseCode ?? '',
                ] : null,
                'subject' => $courseSubject->subject ? [
                    'subjectId' => $courseSubject->subject->subjectId,
                    'subjectCode' => $courseSubject->subject->subjectCode ?? '',
                ] : null,
            ]
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
            'message' => 'Course subject created successfully',
            'data' => [
                'courseSubjectId' => $courseSubject->courseSubjectId,
                'courseId' => $courseSubject->courseId,
                'subjectId' => $courseSubject->subjectId,
            ]
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
            'message' => 'Course subject updated successfully',
            'data' => [
                'courseSubjectId' => $courseSubject->courseSubjectId,
                'courseId' => $courseSubject->courseId,
                'subjectId' => $courseSubject->subjectId,
            ]
        ], 200);
    }

    public function fetchCourseSubjectWithForeignKeys(Request $request)
    {
        $subjectId = $request->query('subjectId');
        $courseId = $request->query('courseId');

        $courseSubject = $this->courseSubjectService->fetchCourseSubjectWithForeignKeys(
            $subjectId,
            $courseId
        );

        if (!$courseSubject) {
            return response()->json(null, 404);
        }

        // Return just the data
        return response()->json($courseSubject, 200);
    }
}
