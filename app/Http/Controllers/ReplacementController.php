<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ReplacementAppeal;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReplacementController extends Controller
{
    /**
     * Display the replacement appeal form
     * This is where students submit their replacement appeal
     */
    public function showAppealForm(Request $request)
    {
        // Get the requested class from timetable
        $requestedClassId = $request->input('class_id');
        
        if (!$requestedClassId) {
            // If no class selected, show available classes for appeal
            $availableClasses = Timetable::with(['subject', 'venue', 'classGroup'])
                ->where('classDate', '>=', now()->format('Y-m-d'))
                ->orderBy('classDate')
                ->orderBy('startTime')
                ->get();
            
            return view('student.replacementModule.select_class', compact('availableClasses'));
        }

        $replacementClass = Timetable::with(['subject', 'venue', 'classGroup'])
            ->find($requestedClassId);

        if (!$replacementClass) {
            return redirect()->route('replacement.appeal')->with('error', 'Requested class not found.');
        }

        // Get student info
        $student = Auth::user();

        // For demo: Get student's original class (you would get this from session or previous page)
        // In a real scenario, this would come from the student's enrolled classes
        $originalClass = Timetable::with(['subject', 'venue', 'classGroup'])
            ->where('courseSubjectId', $replacementClass->courseSubjectId)
            ->where('timetableId', '!=', $replacementClass->timetableId)
            ->first();

        return view('student.replacementModule.appeal_form', compact('originalClass', 'replacementClass', 'student'));
    }

    /**
     * Submit the replacement appeal
     */
    public function submitAppeal(Request $request)
    {
        // Validate the form input
        $validated = $request->validate([
            'original_class_id' => 'required|string|exists:timetable,timetableId',
            'requested_class_id' => 'required|string|exists:timetable,timetableId',
            'reason' => 'required|string|min:10|max:1000',
            'proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120' // 5MB max
        ]);

        // Handle file upload if provided
        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('appeal_proofs', 'public');
        }

        // Get the classes to extract timeslots
        $originalClass = Timetable::find($validated['original_class_id']);
        $requestedClass = Timetable::find($validated['requested_class_id']);

        // Create the appeal - Observer will auto-generate appealId
        $appeal = ReplacementAppeal::create([
            'userId' => Auth::user()->userId,
            'originalClassId' => $validated['original_class_id'],
            'requestedClassId' => $validated['requested_class_id'],
            'originalTimeslot' => $originalClass->startTime,
            'requestedTimeslot' => $requestedClass->startTime,
            'reason' => $validated['reason'],
            'proof' => $proofPath,
            'status' => 'Pending',
            'submissionDate' => now(),
        ]);

        return redirect()->route('replacement.myAppeals')->with('success', 'Your replacement appeal has been submitted successfully! Appeal ID: #' . $appeal->appealId);
    }

    /**
     * Display student's appeal history and status
     */
    public function myAppeals()
    {
        // Get current student's appeals with relationships
        $appeals = ReplacementAppeal::with([
            'originalClass.subject',
            'originalClass.venue',
            'originalClass.classGroup',
            'requestedClass.subject',
            'requestedClass.venue',
            'requestedClass.classGroup',
            'adminUser'
        ])
        ->where('userId', Auth::user()->userId)
        ->orderBy('submissionDate', 'desc')
        ->get();

        return view('student.replacementModule.my_appeals', compact('appeals'));
    }

    /**
     * Admin: View all replacement appeals
     */
    public function adminViewAppeals(Request $request)
    {
        // Filter by status
        $filterStatus = $request->input('status', 'all');

        // Get appeals with relationships
        $query = ReplacementAppeal::with([
            'user',
            'originalClass.subject',
            'originalClass.venue',
            'originalClass.classGroup',
            'requestedClass.subject',
            'requestedClass.venue',
            'requestedClass.classGroup',
            'adminUser'
        ]);

        // Filter appeals based on status
        if ($filterStatus !== 'all') {
            $query->where('status', $filterStatus);
        }

        $appeals = $query->orderBy('submissionDate', 'desc')->get();

        // Calculate statistics
        $allAppeals = ReplacementAppeal::all();
        $stats = [
            'total' => $allAppeals->count(),
            'pending' => $allAppeals->where('status', 'Pending')->count(),
            'approved' => $allAppeals->where('status', 'Approved')->count(),
            'rejected' => $allAppeals->where('status', 'Rejected')->count()
        ];

        return view('admin.replacementModule.appeal_list', compact('appeals', 'stats', 'filterStatus'));
    }

    /**
     * Admin: View single appeal details
     */
    public function adminViewAppealDetail($appealId)
    {
        // Get appeal with all relationships
        $appeal = ReplacementAppeal::with([
            'user',
            'originalClass.subject',
            'originalClass.venue',
            'originalClass.classGroup',
            'requestedClass.subject',
            'requestedClass.venue',
            'requestedClass.classGroup',
            'adminUser'
        ])->findOrFail($appealId);

        // Validation checks
        $validationChecks = [
            'same_week' => true, // You can add logic to check if same week
            'after_submission' => true, // Check if requested class is after submission
            'no_clash' => true, // Check for time clashes
            'has_capacity' => true, // Check if class has capacity
            'valid_account' => $appeal->user->role === 'student', // Check user is student
            'same_subject' => $appeal->originalClass->subjectId === $appeal->requestedClass->subjectId
        ];

        return view('admin.replacementModule.appeal_detail', compact('appeal', 'validationChecks'));
    }

    /**
     * Admin: Process appeal (Approve/Reject)
     */
    public function adminProcessAppeal(Request $request, $appealId)
    {
        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'admin_remarks' => 'nullable|string|max:500'
        ]);

        $appeal = ReplacementAppeal::findOrFail($appealId);

        // Update appeal status
        $appeal->status = $validated['decision'] === 'approve' ? 'Approved' : 'Rejected';
        $appeal->decisionDate = now();
        $appeal->adminId = Auth::user()->userId;
        $appeal->adminRemarks = $validated['admin_remarks'];
        $appeal->save();

        // Observer will automatically create Replacement record if approved

        $message = $validated['decision'] === 'approve' 
            ? "Appeal #$appealId has been approved successfully! Replacement record created automatically." 
            : "Appeal #$appealId has been rejected.";

        return redirect()->route('admin.replacement.appeals')->with('success', $message);
    }
}
