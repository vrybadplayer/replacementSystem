<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Facades\CourseSubjectManagementFacade;
use App\Models\Subject;
use App\Models\CourseSubject;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{

    public function subjectListView(Request $request)
    {
        $allSubjects = CourseSubjectManagementFacade::getSubjectsForViewFromApi();

        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $total = count($allSubjects);
        $items = array_slice($allSubjects, ($currentPage - 1) * $perPage, $perPage);

        $subjects = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.courseModule.listSubject', compact('subjects'));
    }

    public function create()
    {
        return view('admin.courseModule.addSubject');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subjectId' => 'nullable|string|unique:subject,subjectId',
            'subjectCode' => 'required|string|unique:subject,subjectCode',
            'subjectName' => 'required|string|unique:subject,subjectName|different:subjectCode',
            'creditHours' => 'required|integer',
            'category' => 'required|string|in:core,general,elective',
            'status' => 'required|string|in:active,inactive',
        ]);

        if (empty($validated['subjectId'])) {
            $subjects = CourseSubjectManagementFacade::fetchSubjectsFromApi() ?? [];
            $subjectNumbers = [];
            foreach ($subjects as $subject) {
                $subjectId = $subject['subjectId'] ?? '';
                if (strpos($subjectId, 'S') === 0 && is_numeric(substr($subjectId, 1))) {
                    $subjectNumbers[] = (int) substr($subjectId, 1);
                }
            }
            
            $nextNumber = !empty($subjectNumbers) ? max($subjectNumbers) + 1 : 1;
            $validated['subjectId'] = 'S' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }

        $response = CourseSubjectManagementFacade::createSubjectViaApi($validated);
        
        if ($response->successful()) {
            return redirect()->route('admin.subjects')
                ->with('success', 'Subject created successfully!');
        }
        
        $errorMessage = $response->json()['message'] ?? 'Failed to create subject';
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => $errorMessage]);
    }

    public function edit(string $subjectId)
    {
        $subject = CourseSubjectManagementFacade::getSubjectByIdFromApi($subjectId);
        return view('admin.courseModule.editSubject', compact('subject'));
    }

    public function update(Request $request, string $subjectId)
    {
        $validated = $request->validate([
            'subjectCode' => 'required|string|unique:subject,subjectCode,' . $subjectId . ',subjectId',
            'subjectName' => 'required|string|unique:subject,subjectName,' . $subjectId . ',subjectId|different:subjectCode',
            'creditHours' => 'required|integer',
            'category' => 'required|string|in:core,general,elective',
            'status' => 'required|string|in:active,inactive',
        ]);

        $response = CourseSubjectManagementFacade::updateSubjectViaApi($subjectId, $validated);
        
        if ($response->successful()) {
            return redirect()->route('admin.subjects')
                ->with('success', 'Subject updated successfully!');
        }
        
        $errorMessage = $response->json()['message'] ?? 'Failed to update subject';
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => $errorMessage]);
    }


    /**
     * REST API
     */
    public function apiGetAllSubjects()
    {
        try {
            $data = Subject::all();

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

    public function apiCreateSubject(Request $request)
    {
        // Authorization check - only admins can create subjects
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can create subjects.',
            ], 403);
        }

        $validated = $request->validate([
            'subjectId' => 'nullable|string|unique:subject,subjectId',
            'subjectCode' => 'required|string|unique:subject,subjectCode',
            'subjectName' => 'required|string|unique:subject,subjectName|different:subjectCode',
            'creditHours' => 'required|integer',
            'category' => 'required|string|in:core,general,elective',
            'status' => 'required|string|in:active,inactive',
        ]);

        try {
            $subject = Subject::create($validated);

            return response()->json([
                'success' => true,
                'data' => $subject,
            ], 201);
        } finally {
            // Close database connection as soon as possible
            DB::disconnect();
        }
    }

    public function apiGetSubject(string $subjectId)
    {
        $subject = Subject::findOrFail($subjectId);

        return response()->json([
            'success' => true,
            'data' => $subject,
        ], 200);
    }

    public function apiUpdateSubject(Request $request, string $id)
    {
        $subject = Subject::findOrFail($id);
        $subject->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $subject,
        ], 200);
    }

    // GET /api/subject/byCourseSubject/{courseSubjectId}
    public function getSubjectByCourseSubject($courseSubjectId)
    {
        $courseSubject = CourseSubject::where('courseSubjectId', $courseSubjectId)->first();

        $subject = Subject::where('subjectId', $courseSubject->subjectId)
            ->select('subjectCode', 'subjectName')
            ->first();

        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $subject
        ]);
    }
}
