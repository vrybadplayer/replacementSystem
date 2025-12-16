<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Facades\CourseSubjectManagementFacade;
use App\Models\Subject;
use Illuminate\Pagination\LengthAwarePaginator;

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
            'subjectId' => 'required|string',
            'subjectCode' => 'nullable|string',
            'subjectName' => 'required|string',
            'creditHours' => 'nullable|integer',
            'category' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);
        CourseSubjectManagementFacade::createOrUpdateSubject($validated);
        return redirect()->route('admin.subjects')
            ->with('success', 'Subject created successfully!');
    }

    public function edit(string $subjectId)
    {
        $subject = CourseSubjectManagementFacade::getSubjectByIdFromApi($subjectId);
        return view('admin.courseModule.editSubject', compact('subject'));
    }

    public function update(Request $request, string $subjectId)
    {
        $validated = $request->validate([
            'subjectCode' => 'nullable|string',
            'subjectName' => 'required|string',
            'creditHours' => 'nullable|integer',
            'category' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);
        $validated['subjectId'] = $subjectId;
        CourseSubjectManagementFacade::createOrUpdateSubject($validated, $subjectId);
        return redirect()->route('admin.subjects')
            ->with('success', 'Subject updated successfully!');
    }


    /**
     * REST API
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

    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'subjectId' => 'required|string|unique:subject,subjectId',
            'subjectCode' => 'nullable|string',
            'subjectName' => 'required|string',
            'creditHours' => 'nullable|integer',
            'category' => 'nullable|string',
            'status' => 'nullable|string',
        ]);


        $subject = Subject::create($validated);

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
    }

    public function apiShow(string $subjectId)
    {

        $subject = Subject::findOrFail($subjectId);

        return response()->json([
            'success' => true,
            'data' => [
                'subjectId' => $subject->subjectId,
                'subjectCode' => $subject->subjectCode ?? '',
                'subjectName' => $subject->subjectName ?? '',
                'creditHours' => $subject->creditHours ?? 0,
                'category' => $subject->category ?? '',
                'status' => $subject->status ?? 'active',
            ]
        ], 200);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $validated = $request->validate([
            'subjectCode' => 'nullable|string',
            'subjectName' => 'nullable|string',
            'creditHours' => 'nullable|integer',
            'category' => 'nullable|string',
            'status' => 'nullable|string',
        ]);


        $subject = Subject::findOrFail($id);
        $subject->update($validated);

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
    }
}
