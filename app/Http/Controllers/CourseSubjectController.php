<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Facades\CourseSubjectManagementFacade;
use App\Models\CourseSubject;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseSubjectController extends Controller
{

    public function courseSubjectListView(Request $request)
    {
        $search = $request->query('search');
        $allCourseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi();

        $enrichedCourseSubjects = [];
        foreach ($allCourseSubjects as $courseSubject) {
            try {
                $courseSubjectId = $courseSubject['courseSubjectId'];

                if (!$courseSubjectId) {
                    continue;
                }

                $codes = CourseSubjectManagementFacade::getCourseSubjectCodes($courseSubjectId);

                if ($codes) {
                    $courseSubject['course'] = [
                        'courseId' => $codes['courseId'],
                        'courseCode' => $codes['courseCode'],
                        'courseName' => $codes['courseName'],
                    ];

                    $courseSubject['subject'] = [
                        'subjectId' => $codes['subjectId'],
                        'subjectCode' => $codes['subjectCode'],
                        'subjectName' => $codes['subjectName'] ,
                    ];
                }

                $enrichedCourseSubjects[] = $courseSubject;
            } catch (\Exception $e) {
                $csId = $courseSubject['courseSubjectId'];
                Log::warning("Failed to enrich course subject {$csId}: " . $e->getMessage());
            }
        }

        if ($search) {
            $enrichedCourseSubjects = collect($enrichedCourseSubjects)->filter(function ($cs) use ($search) {
                $searchLower = Str::lower($search);
                $courseId = $cs['courseId'];
                $subjectId = $cs['subjectId'];
                $courseCode = isset($cs['course']) ? ($cs['course']['courseCode']) : '';
                $subjectCode = isset($cs['subject']) ? ($cs['subject']['subjectCode']) : '';
                
                return Str::contains(Str::lower($cs['courseSubjectId']), $searchLower) ||
                       Str::contains(Str::lower($courseId), $searchLower) ||
                       Str::contains(Str::lower($subjectId), $searchLower) ||
                       Str::contains(Str::lower($courseCode), $searchLower) ||
                       Str::contains(Str::lower($subjectCode), $searchLower);
            })->values()->all();
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

        return view('admin.courseModule.listCourseSubject', compact('courseSubjects', 'search'));
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

        $course = CourseSubjectManagementFacade::getCourseByIdFromApi($validated['courseId']);
        $subject = CourseSubjectManagementFacade::getSubjectByIdFromApi($validated['subjectId']);
        
        if (!$course || ($course['status']) !== 'active') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['courseId' => 'The selected course is not active.']);
        }
        
        if (!$subject || ($subject['status']) !== 'active') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['subjectId' => 'The selected subject is not active.']);
        }

        $courseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi();
        $existing = null;
        foreach ($courseSubjects as $courseSubject) {
            if (($courseSubject['courseId']) === $validated['courseId'] && 
                ($courseSubject['subjectId']) === $validated['subjectId']) {
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
            $courseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi();
            $courseSubjectNumbers = [];
            foreach ($courseSubjects as $courseSubject) {
                $courseSubjectId = $courseSubject['courseSubjectId'];
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
        
        $errorMessage = $response->json()['message'];
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

        $course = CourseSubjectManagementFacade::getCourseByIdFromApi($validated['courseId']);
        $subject = CourseSubjectManagementFacade::getSubjectByIdFromApi($validated['subjectId']);
        
        if (!$course || ($course['status']) !== 'active') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['courseId' => 'The selected course is not active.']);
        }
        
        if (!$subject || ($subject['status']) !== 'active') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['subjectId' => 'The selected subject is not active.']);
        }

        $courseSubjects = CourseSubjectManagementFacade::fetchCourseSubjectsFromApi();
        $existing = null;
        foreach ($courseSubjects as $courseSubject) {
            if (($courseSubject['courseId']) === $validated['courseId'] && 
                ($courseSubject['subjectId']) === $validated['subjectId'] &&
                ($courseSubject['courseSubjectId'] ) !== $courseSubjectId) {
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
        
        $errorMessage = $response->json()['message'];
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => $errorMessage]);
    }


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
            DB::disconnect();
        }
    }

    public function apiCreateCourseSubject(Request $request)
    {
        try {
            $courseSubject = CourseSubject::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $courseSubject,
            ], 201);
        } finally {
            DB::disconnect();
        }
    }

    public function apiUpdateCourseSubject(Request $request, string $id)
    {
        try {
            $courseSubject = CourseSubject::findOrFail($id);
            $courseSubject->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $courseSubject,
            ], 200);
        } finally {
            DB::disconnect();
        }
    }

    public function fetchCourseSubjectWithForeignKeys(Request $request)
    {
        try {
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
        } finally {
            DB::disconnect();
        }
    }

    public function getCourseSubjectsByCourse($courseId)
    {
        try {
            $courseSubjectIds = CourseSubject::where('courseId', $courseId)
                ->pluck('courseSubjectId')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $courseSubjectIds,
                'count' => count($courseSubjectIds)
            ]);
        } finally {
            DB::disconnect();
        }
    }

    public function getCourseSubject($courseSubjectId)
    {
        try {
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
                    'courseCode' => $courseSubject->course->courseCode,
                    'courseName' => $courseSubject->course->courseName,
                    'subjectId' => $courseSubject->subjectId,
                    'subjectCode' => $courseSubject->subject->subjectCode,
                    'subjectName' => $courseSubject->subject->subjectName,
                ],
            ], 200);
        } finally {
            DB::disconnect();
        }
    }
}
