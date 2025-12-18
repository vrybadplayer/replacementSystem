<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ReplacementAppeal;
use App\Models\Replacement;
use App\Models\Timetable;
use App\Services\TimetableService;
use App\Services\ReplacementService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReplacementController extends Controller
{
    protected $replacementService;
    protected $timetableService;
    protected $userService;

    public function __construct(ReplacementService $replacementService, TimetableService $timetableService, UserService $userService)
    {
        $this->replacementService = $replacementService;
        $this->timetableService = $timetableService;
        $this->userService = $userService;
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
            $availableClasses = $this->getFutureClassesDirectly(Auth::user()->userId);
            return view('student.replacementModule.select_class', compact('availableClasses'));
        }

        // Get replacement class (the class student wants to JOIN) from API
        $replacementClass = $this->replacementService->getTimetableByIdFromApi($requestedClassId);

        if (!$replacementClass) {
            return redirect()->route('replacement.appeal')->with('error', 'Requested class not found.');
        }

        // Get student info
        $student = Auth::user();

        // Get the ORIGINAL class (the class student MISSED) from session
        // This was selected from the timetable calendar view
        $selectedClassData = session('replacement_selected_class');
        
        if (!$selectedClassData) {
            // If no original class selected, redirect to select one
            return redirect()->route('replacement.appeal')->with('error', 'Please select your original missed class first.');
        }

        // Load the original class with relationships
        $originalClass = Timetable::with(['subject', 'venue', 'classGroup'])
            ->where('timetableId', $selectedClassData['timetableId'])
            ->first();

        if (!$originalClass) {
            // If timetable not found, create object from session data
            $originalClass = (object) [
                'timetableId' => $selectedClassData['timetableId'],
                'classDate' => $selectedClassData['classDate'],
                'classType' => $selectedClassData['classType'] ?? 'Lecture',
                'startTime' => $selectedClassData['startTime'],
                'endTime' => $selectedClassData['endTime'],
                'classGroupId' => $selectedClassData['classGroupId'],
                'subject' => (object) [
                    'subjectId' => $selectedClassData['subjectCode'] ?? 'N/A',
                    'subjectName' => $selectedClassData['subjectName'] ?? 'Unknown Subject'
                ],
                'venue' => (object) [
                    'venueId' => $selectedClassData['venueId'] ?? 'N/A'
                ],
                'classGroup' => (object) [
                    'classGroupId' => $selectedClassData['classGroupId'] ?? 'N/A'
                ]
            ];
        } else {
            // If classGroup relationship is null, add it from session or database
            if (!$originalClass->classGroup && $selectedClassData['classGroupId']) {
                $originalClass->classGroup = (object) [
                    'classGroupId' => $selectedClassData['classGroupId']
                ];
            }
        }

        // Clear the session data after using it
        session()->forget('replacement_selected_class');

        return view('student.replacementModule.appeal_form', compact('originalClass', 'replacementClass', 'student'));
    }

    // Helper function for showAppealForm 
    private function getFutureClassesDirectly($userId)
    {
        $studentProfile = $this->userService->getUser($userId);

        $classGroupId = $studentProfile['classGroupId'];
        $today = Carbon::today()->format('Y-m-d');

        return Timetable::with(['venue', 'courseSubject.subject'])
            ->where(function ($query) use ($classGroupId) {
                $query->where('classGroupId', $classGroupId)
                    ->orWhereRaw("FIND_IN_SET(?, classGroupId) > 0", [$classGroupId]);
            })
            ->where('classDate', '>', $today)
            ->orderBy('classDate')
            ->orderBy('startTime')
            ->get();
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
        
        // Get student's actual classGroupId
        $studentClassGroupId = Auth::user()->classGroupId;
        
        // Extract the appropriate classGroupId from the timetable records
        // If the timetable has multiple groups (comma-separated), check if student's group is in it
        $originalClassGroupId = $this->extractStudentClassGroup($originalClass->classGroupId, $studentClassGroupId);
        $requestedClassGroupId = $this->extractStudentClassGroup($requestedClass->classGroupId, $studentClassGroupId);

        // Create the appeal - Observer will auto-generate appealId
        $appeal = ReplacementAppeal::create([
            'userId' => Auth::user()->userId,
            'originalTimetableId' => $validated['original_class_id'],
            'requestedTimetableId' => $validated['requested_class_id'],
            'originalClassGroupId' => $originalClassGroupId,
            'requestedClassGroupId' => $requestedClassGroupId,
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
     * Helper: Extract student's classGroupId from potentially comma-separated list
     */
    private function extractStudentClassGroup($classGroupIds, $studentClassGroupId)
    {
        // If it's a single group, return it
        if (strpos($classGroupIds, ',') === false) {
            return $classGroupIds;
        }
        
        // If multiple groups, check if student's group is in the list
        $groups = array_map('trim', explode(',', $classGroupIds));
        
        if (in_array($studentClassGroupId, $groups)) {
            return $studentClassGroupId;
        }
        
        // Otherwise return the first group
        return $groups[0];
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
            // Check if replacement class is in the same week as original
            'same_week' => function() use ($appeal) {
                if (!$appeal->originalClass->date || !$appeal->requestedClass->date) return false;
                $originalWeek = date('W', strtotime($appeal->originalClass->date));
                $requestedWeek = date('W', strtotime($appeal->requestedClass->date));
                return $originalWeek === $requestedWeek;
            },
            
            // Check if requested class is after submission date
            'after_submission' => function() use ($appeal) {
                if (!$appeal->requestedClass->date) return false;
                return strtotime($appeal->requestedClass->date) > strtotime($appeal->submissionDate);
            },
            
            // Check for timetable clash - requested class shouldn't clash with student's existing schedule
            'no_clash' => function() use ($appeal) {
                if (!$appeal->requestedClass->date || !$appeal->requestedClass->startTime || !$appeal->requestedClass->endTime) {
                    return false;
                }
                
                // Get student's timetable for that date
                $studentTimetable = Timetable::where(function($query) use ($appeal) {
                        $query->where('classGroupId', $appeal->user->classGroupId)
                              ->orWhereRaw("FIND_IN_SET(?, classGroupId) > 0", [$appeal->user->classGroupId]);
                    })
                    ->where('classDate', $appeal->requestedClass->date)
                    ->get();
                
                // Check for time overlaps
                foreach ($studentTimetable as $class) {
                    $existingStart = strtotime($class->startTime);
                    $existingEnd = strtotime($class->endTime);
                    $requestedStart = strtotime($appeal->requestedClass->startTime);
                    $requestedEnd = strtotime($appeal->requestedClass->endTime);
                    
                    // Check if times overlap
                    if ($requestedStart < $existingEnd && $requestedEnd > $existingStart) {
                        return false; // Clash detected
                    }
                }
                
                return true; // No clash
            },
            
            // Check if class has available capacity (< 5 replacement students)
            'has_capacity' => function() use ($appeal) {
                if (!isset($appeal->requestedClass)) return false;
                
                // Get the requested class group ID from the nested structure
                $requestedGroupId = $appeal->requestedClass->classGroup->classGroupId ?? null;
                
                if (!$requestedGroupId) return false;
                
                // Count approved replacements for this class group
                $replacementCount = Replacement::where('requestedClassGroupId', $requestedGroupId)
                    ->whereHas('appeal', function($query) {
                        $query->where('status', 'Approved');
                    })
                    ->count();
                
                return $replacementCount < 5;
            },
            
            // Check if student account is active
            'valid_account' => $appeal->user->role === 'student' && $appeal->user->status === 'active',
            
            // Check if same subject code
            'same_subject' => $appeal->originalClass->subject && $appeal->requestedClass->subject ?
                $appeal->originalClass->subject->subjectId === $appeal->requestedClass->subject->subjectId : false
        ];
        
        // Execute all validation functions
        $validationChecks = array_map(function($check) {
            return is_callable($check) ? $check() : $check;
        }, $validationChecks);

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
    // API METHODS (Previously in ReplacementApiController)
    // ============================================

    /**
     * API: Get all appeals (admin)
     */
    public function getAllAppeals()
    {
        try {
            $appeals = ReplacementAppeal::with(['user', 'adminUser', 'replacement'])
                ->orderBy('submissionDate', 'desc')
                ->get()
                ->map(function ($appeal) {
                    // Get the specific timetable records using stored timetableIds
                    $originalTimetable = $appeal->originalTimetableId 
                        ? Timetable::with(['subject', 'venue'])->find($appeal->originalTimetableId)
                        : null;
                    
                    $requestedTimetable = $appeal->requestedTimetableId
                        ? Timetable::with(['subject', 'venue'])->find($appeal->requestedTimetableId)
                        : null;

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
                            'classType' => $originalTimetable->classType ?? null,
                            'classGroupId' => $originalTimetable->classGroupId ?? null,
                            'date' => $originalTimetable->classDate ?? null,
                            'startTime' => $originalTimetable && $originalTimetable->startTime ? date('H:i', strtotime($originalTimetable->startTime)) : null,
                            'endTime' => $originalTimetable && $originalTimetable->endTime ? date('H:i', strtotime($originalTimetable->endTime)) : null,
                            'classGroup' => [
                                'classGroupId' => $appeal->originalClassGroupId ?? null,
                            ],
                        ],
                        'requestedClass' => [
                            'subject' => $requestedTimetable->subject ?? null,
                            'venue' => $requestedTimetable->venue ?? null,
                            'classType' => $requestedTimetable->classType ?? null,
                            'classGroupId' => $requestedTimetable->classGroupId ?? null,
                            'date' => $requestedTimetable->classDate ?? null,
                            'startTime' => $requestedTimetable && $requestedTimetable->startTime ? date('H:i', strtotime($requestedTimetable->startTime)) : null,
                            'endTime' => $requestedTimetable && $requestedTimetable->endTime ? date('H:i', strtotime($requestedTimetable->endTime)) : null,
                            'classGroup' => [
                                'classGroupId' => $appeal->requestedClassGroupId ?? null,
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
            Log::error('Failed to fetch appeals', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve appeals. Please try again later.'
            ], 500);
        }
    }

    /**
     * API: Get user's appeals
     */
    public function getUserAppeals($userId)
    {
        try {
            $appeals = ReplacementAppeal::with(['user', 'adminUser', 'replacement'])
                ->where('userId', $userId)
                ->orderBy('submissionDate', 'desc')
                ->get()
                ->map(function ($appeal) {
                    // Get the specific timetable records using stored timetableIds
                    $originalTimetable = $appeal->originalTimetableId 
                        ? Timetable::with(['subject', 'venue'])->find($appeal->originalTimetableId)
                        : null;
                    
                    $requestedTimetable = $appeal->requestedTimetableId
                        ? Timetable::with(['subject', 'venue'])->find($appeal->requestedTimetableId)
                        : null;

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
                            'classType' => $originalTimetable->classType ?? null,
                            'classGroupId' => $originalTimetable->classGroupId ?? null,
                            'date' => $originalTimetable->classDate ?? null,
                            'startTime' => $originalTimetable && $originalTimetable->startTime ? date('H:i', strtotime($originalTimetable->startTime)) : null,
                            'endTime' => $originalTimetable && $originalTimetable->endTime ? date('H:i', strtotime($originalTimetable->endTime)) : null,
                            'classGroup' => [
                                'classGroupId' => $appeal->originalClassGroupId ?? null,
                            ],
                        ],
                        'requestedClass' => [
                            'subject' => $requestedTimetable->subject ?? null,
                            'venue' => $requestedTimetable->venue ?? null,
                            'classType' => $requestedTimetable->classType ?? null,
                            'classGroupId' => $requestedTimetable->classGroupId ?? null,
                            'date' => $requestedTimetable->classDate ?? null,
                            'startTime' => $requestedTimetable && $requestedTimetable->startTime ? date('H:i', strtotime($requestedTimetable->startTime)) : null,
                            'endTime' => $requestedTimetable && $requestedTimetable->endTime ? date('H:i', strtotime($requestedTimetable->endTime)) : null,
                            'classGroup' => [
                                'classGroupId' => $appeal->requestedClassGroupId ?? null,
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
            Log::error('Failed to fetch user appeals', ['userId' => $userId, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve your appeals. Please try again later.'
            ], 500);
        }
    }

    /**
     * API: Get single appeal details
     */
    public function getAppeal($appealId)
    {
        try {
            $appeal = ReplacementAppeal::with(['user', 'adminUser', 'replacement'])
                ->findOrFail($appealId);

            // Get the specific timetable records using stored timetableIds
            $originalTimetable = $appeal->originalTimetableId 
                ? Timetable::with(['subject', 'venue'])->find($appeal->originalTimetableId)
                : null;
            
            $requestedTimetable = $appeal->requestedTimetableId
                ? Timetable::with(['subject', 'venue'])->find($appeal->requestedTimetableId)
                : null;

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
                    'classType' => $originalTimetable->classType ?? null,
                    'classGroupId' => $originalTimetable->classGroupId ?? null,
                    'date' => $originalTimetable->classDate ?? null,
                    'startTime' => $originalTimetable && $originalTimetable->startTime ? date('H:i', strtotime($originalTimetable->startTime)) : null,
                    'endTime' => $originalTimetable && $originalTimetable->endTime ? date('H:i', strtotime($originalTimetable->endTime)) : null,
                    'classGroup' => [
                        'classGroupId' => $appeal->originalClassGroupId ?? null,
                    ],
                ],
                'requestedClass' => [
                    'subject' => $requestedTimetable->subject ?? null,
                    'venue' => $requestedTimetable->venue ?? null,
                    'classType' => $requestedTimetable->classType ?? null,
                    'classGroupId' => $requestedTimetable->classGroupId ?? null,
                    'date' => $requestedTimetable->classDate ?? null,
                    'startTime' => $requestedTimetable && $requestedTimetable->startTime ? date('H:i', strtotime($requestedTimetable->startTime)) : null,
                    'endTime' => $requestedTimetable && $requestedTimetable->endTime ? date('H:i', strtotime($requestedTimetable->endTime)) : null,
                    'classGroup' => [
                        'classGroupId' => $appeal->requestedClassGroupId ?? null,
                    ],
                ],
                'replacement' => $appeal->replacement,
            ];

            return response()->json([
                'success' => true,
                'data' => $appealData
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch appeal details', ['appealId' => $appealId, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Appeal not found.'
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
            Log::error('Failed to create appeal', ['error' => $e->getMessage(), 'request' => $request->except(['proof'])]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to create appeal. Please check your input and try again.'
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
            Log::error('Failed to process appeal', ['appealId' => $appealId, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to process appeal. Please try again later.'
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
            Log::error('Failed to fetch user replacements', ['userId' => $userId, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve replacements. Please try again later.'
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
            Log::error('Failed to fetch statistics', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve statistics. Please try again later.'
            ], 500);
        }
    }


    public function validReplacementOptions(Request $request)
    {
        $originalClassId = $request->query('originalClassId');
        $originalDate = $request->query('originalDate');
        $myClassGroupId = $request->query('classGroupId');

        $originalClass = Timetable::with('courseSubject')->find($originalClassId); // Fetch From API
        $subjectId = $originalClass->courseSubject->subjectId;
        $classType = $originalClass->classType;

        if (!$originalClass) {
            return response()->json(['data' => [], 'error' => 'Original class not found'], 404);
        }

        // After today, in same week
        $startOfWeek = Carbon::parse($originalDate)->startOfWeek()->format('Y-m-d');
        $endOfWeek = Carbon::parse($originalDate)->endOfWeek()->format('Y-m-d');

        // Consider using API
        $validSlots = Timetable::with(['venue', 'courseSubject.subject'])
            // courseSubject -> subjectId
            ->whereHas('courseSubject', function ($query) use ($subjectId) {
                $query->where('subjectId', $subjectId);
            })
            ->where('classType', $classType)
            ->where('classMode', 'weekly')
            ->where('timetableId', '!=', $originalClassId) // != same class
            ->where('classDate', '>', Carbon::today()->format('Y-m-d'))
            ->whereBetween('classDate', [$startOfWeek, $endOfWeek])
            // != same group
            ->where(function ($query) use ($myClassGroupId) {
                $query->where('classGroupId', 'NOT LIKE', "%{$myClassGroupId}%");
            })
            ->orderBy('classDate')
            ->orderBy('startTime')
            ->get();

        $finalData = $validSlots->map(function ($slot) {
            return [
                'timetableId' => $slot->timetableId,
                'subjectCode' => $slot->courseSubject->subject->subjectCode ?? 'N/A',
                'dayOfWeek' => $slot->dayOfWeek,
                'startTime' => $slot->startTime,
                'endTime' => $slot->endTime,
                'venueId' => $slot->venueId,
                'classDate' => $slot->classDate,
                'type' => $slot->classType,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $finalData
        ]);
    }
}
