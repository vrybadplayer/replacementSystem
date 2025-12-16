<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ReplacementAppeal;
use App\Models\Timetable;
use App\Models\User;
use App\Services\ReplacementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReplacementController extends Controller
{
    protected $replacementService;

    public function __construct(ReplacementService $replacementService)
    {
        $this->replacementService = $replacementService;
    }

    /**
     * Get all replacement appeals (API format)
     * Returns all appeals from database in JSON format
     */
    public function index()
    {
        try {
            $appeals = ReplacementAppeal::with([
                'user',
                'originalClass.subject',
                'originalClass.venue',
                'originalClass.classGroup',
                'requestedClass.subject',
                'requestedClass.venue',
                'requestedClass.classGroup',
                'adminUser',
                'replacement'
            ])->orderBy('submissionDate', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $appeals,
                'count' => $appeals->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appeals: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the replacement appeal form
     * This is where students submit their replacement appeal
     * Calls ReplacementService which makes API calls to port 8001
     */
    public function showAppealForm(Request $request)
    {
        // Get the requested class from timetable
        $requestedClassId = $request->input('class_id');
        
        if (!$requestedClassId) {
            // If no class selected, show available classes for appeal
            // Call service to get available classes from API
            $availableClasses = $this->replacementService->getAvailableClassesFromApi();
            
            return view('student.replacementModule.select_class', compact('availableClasses'));
        }

        // Get replacement class from API
        $replacementClass = $this->replacementService->getTimetableByIdFromApi($requestedClassId);

        if (!$replacementClass) {
            return redirect()->route('replacement.appeal')->with('error', 'Requested class not found.');
        }

        // Get student info
        $student = Auth::user();

        // For demo: Get student's original class from API
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

        // Get the classes to extract timeslots and class group IDs
        $originalClass = Timetable::find($validated['original_class_id']);
        $requestedClass = Timetable::find($validated['requested_class_id']);

        // Create the appeal - Observer will auto-generate appealId
        $appeal = ReplacementAppeal::create([
            'userId' => Auth::user()->userId,
            'originalClassGroupId' => $originalClass->classGroupId,
            'requestedClassGroupId' => $requestedClass->classGroupId,
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
     * Calls ReplacementService which makes API calls to port 8001
     */
    public function myAppeals()
    {
        $userId = Auth::user()->userId;
        $appeals = $this->replacementService->getUserAppealsFromApi($userId);
        
        return view('student.replacementModule.my_appeals', compact('appeals'));
    }

    /**
     * Admin: View all replacement appeals
     * Calls ReplacementService which makes API calls to port 8001
     */
    public function adminViewAppeals(Request $request)
    {
        $filterStatus = $request->input('status', 'all');

        // Get all appeals and statistics from service
        $allAppeals = $this->replacementService->getAllAppealsFromApi();
        $stats = $this->replacementService->getStatisticsFromApi();
        
        // Filter appeals based on status
        if ($filterStatus !== 'all') {
            $appeals = $allAppeals->where('status', $filterStatus)->values();
        } else {
            $appeals = $allAppeals;
        }
        
        return view('admin.replacementModule.appeal_list', compact('appeals', 'stats', 'filterStatus'));
    }

    /**
     * Admin: View single appeal details
     */
    public function adminViewAppealDetail($appealId)
    {
        // Get appeal from API
        $appeal = $this->replacementService->getAppealFromApi($appealId);
        
        if (!$appeal) {
            abort(404, 'Appeal not found');
        }

        // Validation checks
        $validationChecks = [
            'same_week' => true, // You can add logic to check if same week
            'after_submission' => true, // Check if requested class is after submission
            'no_clash' => true, // Check for time clashes
            'has_capacity' => true, // Check if class has capacity
            'valid_account' => $appeal->user->role === 'student', // Check user is student
            'same_subject' => $appeal->originalClass->subject && $appeal->requestedClass->subject ? 
                $appeal->originalClass->subject->subjectId === $appeal->requestedClass->subject->subjectId : false
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

        $appeal = ReplacementAppeal::with('requestedClass')->findOrFail($appealId);

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

    // ============================================
    // API METHODS (Previously in ReplacementApiController and TimetableController)
    // ============================================

    /**
     * API: Get all timetable records
     */
    public function getAllTimetableRecords()
    {
        $timetable = Timetable::all();
        
        return response()->json([
            'success' => true,
            'data' => $timetable
        ]);
    }

    /**
     * API: Get single timetable record by ID
     */
    public function getTimetableById($id)
    {
        $timetable = Timetable::with(['subject', 'venue', 'classGroup'])->find($id);
        
        if (!$timetable) {
            return response()->json([
                'success' => false,
                'message' => 'Timetable record not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $timetable
        ]);
    }

    /**
     * API: Get all available classes for replacement
     */
    public function getAvailableClasses()
    {
        try {
            $classes = Timetable::with(['subject', 'venue', 'classGroup'])
                ->where('classDate', '>=', now()->format('Y-m-d'))
                ->orderBy('classDate')
                ->orderBy('startTime')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $classes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available classes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get all appeals (admin)
     */
    public function getAllAppeals()
    {
        try {
            $appeals = ReplacementAppeal::with([
                'user',
                'originalClass.timetables.subject',
                'originalClass.timetables.venue',
                'requestedClass.timetables.subject',
                'requestedClass.timetables.venue',
                'adminUser',
                'replacement'
            ])->orderBy('submissionDate', 'desc')
            ->get()
            ->map(function($appeal) {
                // Flatten the structure for easier view access
                $originalTimetable = $appeal->originalClass->timetables->first();
                $requestedTimetable = $appeal->requestedClass->timetables->first();
                
                return [
                    'appealId' => $appeal->appealId,
                    'userId' => $appeal->userId,
                    'status' => $appeal->status,
                    'reason' => $appeal->reason,
                    'proof' => $appeal->proof,
                    'submissionDate' => $appeal->submissionDate ? $appeal->submissionDate->format('Y-m-d H:i:s') : null,
                    'decisionDate' => $appeal->decisionDate ? $appeal->decisionDate->format('Y-m-d H:i:s') : null,
                    'adminRemarks' => $appeal->adminRemarks,
                    'user' => $appeal->user,
                    'adminUser' => $appeal->adminUser,
                    'originalClass' => [
                        'subject' => $originalTimetable->subject ?? null,
                        'venue' => $originalTimetable->venue ?? null,
                        'date' => $originalTimetable->classDate ?? null,
                        'startTime' => $originalTimetable && $originalTimetable->startTime ? date('H:i', strtotime($originalTimetable->startTime)) : null,
                        'endTime' => $originalTimetable && $originalTimetable->endTime ? date('H:i', strtotime($originalTimetable->endTime)) : null,
                        'classGroup' => [
                            'classGroupId' => $appeal->originalClass->classGroupId ?? null,
                        ],
                    ],
                    'requestedClass' => [
                        'subject' => $requestedTimetable->subject ?? null,
                        'venue' => $requestedTimetable->venue ?? null,
                        'date' => $requestedTimetable->classDate ?? null,
                        'startTime' => $requestedTimetable && $requestedTimetable->startTime ? date('H:i', strtotime($requestedTimetable->startTime)) : null,
                        'endTime' => $requestedTimetable && $requestedTimetable->endTime ? date('H:i', strtotime($requestedTimetable->endTime)) : null,
                        'classGroup' => [
                            'classGroupId' => $appeal->requestedClass->classGroupId ?? null,
                        ],
                    ],
                    'replacement' => $appeal->replacement,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $appeals
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appeals: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get pending appeals (admin)
     */
    public function getPendingAppeals()
    {
        try {
            $appeals = ReplacementAppeal::with([
                'user',
                'originalClass.subject',
                'requestedClass.subject'
            ])
            ->where('status', 'Pending')
            ->orderBy('submissionDate', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $appeals
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending appeals: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get user's appeals
     */
    public function getUserAppeals($userId)
    {
        try {
            $appeals = ReplacementAppeal::with([
                'user',
                'originalClass.timetables.subject',
                'originalClass.timetables.venue',
                'requestedClass.timetables.subject',
                'requestedClass.timetables.venue',
                'adminUser',
                'replacement'
            ])
            ->where('userId', $userId)
            ->orderBy('submissionDate', 'desc')
            ->get()
            ->map(function($appeal) {
                // Flatten the structure for easier view access
                $originalTimetable = $appeal->originalClass->timetables->first();
                $requestedTimetable = $appeal->requestedClass->timetables->first();
                
                return [
                    'appealId' => $appeal->appealId,
                    'userId' => $appeal->userId,
                    'status' => $appeal->status,
                    'reason' => $appeal->reason,
                    'proof' => $appeal->proof,
                    'submissionDate' => $appeal->submissionDate ? $appeal->submissionDate->format('Y-m-d H:i:s') : null,
                    'decisionDate' => $appeal->decisionDate ? $appeal->decisionDate->format('Y-m-d H:i:s') : null,
                    'adminRemarks' => $appeal->adminRemarks,
                    'user' => $appeal->user,
                    'adminUser' => $appeal->adminUser,
                    'originalClass' => [
                        'subject' => $originalTimetable->subject ?? null,
                        'venue' => $originalTimetable->venue ?? null,
                        'date' => $originalTimetable->classDate ?? null,
                        'startTime' => $originalTimetable && $originalTimetable->startTime ? date('H:i', strtotime($originalTimetable->startTime)) : null,
                        'endTime' => $originalTimetable && $originalTimetable->endTime ? date('H:i', strtotime($originalTimetable->endTime)) : null,
                        'classGroup' => [
                            'classGroupId' => $appeal->originalClass->classGroupId ?? null,
                        ],
                    ],
                    'requestedClass' => [
                        'subject' => $requestedTimetable->subject ?? null,
                        'venue' => $requestedTimetable->venue ?? null,
                        'date' => $requestedTimetable->classDate ?? null,
                        'startTime' => $requestedTimetable && $requestedTimetable->startTime ? date('H:i', strtotime($requestedTimetable->startTime)) : null,
                        'endTime' => $requestedTimetable && $requestedTimetable->endTime ? date('H:i', strtotime($requestedTimetable->endTime)) : null,
                        'classGroup' => [
                            'classGroupId' => $appeal->requestedClass->classGroupId ?? null,
                        ],
                    ],
                    'replacement' => $appeal->replacement,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $appeals
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user appeals: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get single appeal details
     */
    public function getAppeal($appealId)
    {
        try {
            $appeal = ReplacementAppeal::with([
                'user',
                'originalClass.timetables.subject',
                'originalClass.timetables.venue',
                'requestedClass.timetables.subject',
                'requestedClass.timetables.venue',
                'adminUser',
                'replacement'
            ])->findOrFail($appealId);

            // Flatten the structure for easier view access
            $originalTimetable = $appeal->originalClass->timetables->first();
            $requestedTimetable = $appeal->requestedClass->timetables->first();
            
            $appealData = [
                'appealId' => $appeal->appealId,
                'userId' => $appeal->userId,
                'status' => $appeal->status,
                'reason' => $appeal->reason,
                'proof' => $appeal->proof,
                'submissionDate' => $appeal->submissionDate ? $appeal->submissionDate->format('Y-m-d H:i:s') : null,
                'decisionDate' => $appeal->decisionDate ? $appeal->decisionDate->format('Y-m-d H:i:s') : null,
                'adminRemarks' => $appeal->adminRemarks,
                'user' => $appeal->user,
                'adminUser' => $appeal->adminUser,
                'originalClass' => [
                    'subject' => $originalTimetable->subject ?? null,
                    'venue' => $originalTimetable->venue ?? null,
                    'date' => $originalTimetable->classDate ?? null,
                    'startTime' => $originalTimetable && $originalTimetable->startTime ? date('H:i', strtotime($originalTimetable->startTime)) : null,
                    'endTime' => $originalTimetable && $originalTimetable->endTime ? date('H:i', strtotime($originalTimetable->endTime)) : null,
                    'classGroup' => [
                        'classGroupId' => $appeal->originalClass->classGroupId ?? null,
                    ],
                ],
                'requestedClass' => [
                    'subject' => $requestedTimetable->subject ?? null,
                    'venue' => $requestedTimetable->venue ?? null,
                    'date' => $requestedTimetable->classDate ?? null,
                    'startTime' => $requestedTimetable && $requestedTimetable->startTime ? date('H:i', strtotime($requestedTimetable->startTime)) : null,
                    'endTime' => $requestedTimetable && $requestedTimetable->endTime ? date('H:i', strtotime($requestedTimetable->endTime)) : null,
                    'classGroup' => [
                        'classGroupId' => $appeal->requestedClass->classGroupId ?? null,
                    ],
                ],
                'replacement' => $appeal->replacement,
            ];

            return response()->json([
                'success' => true,
                'data' => $appealData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Appeal not found: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * API: Create new appeal
     */
    public function createAppeal(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,userId',
                'original_class_id' => 'required|exists:timetable,timetableId',
                'requested_class_id' => 'required|exists:timetable,timetableId',
                'original_timeslot' => 'required|string',
                'reason' => 'required|string|min:10',
                'proof' => 'nullable|string'
            ]);

            $appeal = ReplacementAppeal::create([
                'userId' => $validated['user_id'],
                'originalClassGroupId' => $validated['original_class_id'],
                'requestedClassGroupId' => $validated['requested_class_id'],
                'originalTimeslot' => $validated['original_timeslot'],
                'reason' => $validated['reason'],
                'proof' => $validated['proof'] ?? null,
                'status' => 'Pending',
                'submissionDate' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appeal created successfully',
                'data' => $appeal
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create appeal: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Process appeal (approve/reject)
     */
    public function processAppeal(Request $request, $appealId)
    {
        try {
            $validated = $request->validate([
                'decision' => 'required|in:approve,reject',
                'admin_id' => 'required|exists:users,userId',
                'admin_remarks' => 'nullable|string|max:500'
            ]);

            $appeal = ReplacementAppeal::findOrFail($appealId);
            
            $appeal->status = $validated['decision'] === 'approve' ? 'Approved' : 'Rejected';
            $appeal->decisionDate = now();
            $appeal->adminId = $validated['admin_id'];
            $appeal->adminRemarks = $validated['admin_remarks'] ?? null;
            $appeal->save();

            return response()->json([
                'success' => true,
                'message' => 'Appeal processed successfully',
                'data' => $appeal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process appeal: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Get user's replacements
     */
    public function getUserReplacements($userId)
    {
        try {
            $appeals = ReplacementAppeal::with([
                'originalClass.subject',
                'requestedClass.subject',
                'replacement'
            ])
            ->where('userId', $userId)
            ->where('status', 'Approved')
            ->whereHas('replacement')
            ->orderBy('decisionDate', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $appeals
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch replacements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total_appeals' => ReplacementAppeal::count(),
                'pending_appeals' => ReplacementAppeal::where('status', 'Pending')->count(),
                'approved_appeals' => ReplacementAppeal::where('status', 'Approved')->count(),
                'rejected_appeals' => ReplacementAppeal::where('status', 'Rejected')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
