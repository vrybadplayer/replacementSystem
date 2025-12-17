<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use Illuminate\Http\Request;
use App\Services\TimetableService;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TimetableController extends Controller
{

    protected $timetableService;
    protected $userService;

    public function __construct(TimetableService $timetableService, UserService $userService)
    {
        $this->timetableService = $timetableService;
        $this->userService = $userService;
    }

    // GET /api/timetable
    public function getAllTimetableRecords()
    {
        $timetable = Timetable::all();
        return response()->json(['data' => $timetable]);
    }

    // GET /api/timetable/{id}
    public function getTimetableById($id)
    {
        $record = Timetable::find($id);
        if (!$record) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json(['data' => $record]);
    }

    // GET /api/occupiedSlots
    public function getOccupiedSlots(Request $request)
    {
        $day = $request->query('day');
        $venueId = $request->query('venueId');

        return Timetable::where('dayOfWeek', $day)
            ->where('venueId', $venueId)
            ->select('startTime', 'endTime')
            ->get();
    }

    // GET /api/check-venue-occupancy
    public function claskCheck(Request $request)
    {

        $day = $request->query('day');
        $venueId = $request->query('venueId');
        $startTime = $request->query('startTime');
        $endTime = $request->query('endTime');

        // Direct database query in controller
        $isOccupied = Timetable::where('dayOfWeek', $day)
            ->where('venueId', $venueId)
            ->where('startTime', '<', $endTime)
            ->where('endTime', '>', $startTime)
            ->exists();

        return response()->json([
            'success' => true,
            'isOccupied' => $isOccupied,
            'message' => $isOccupied ? 'Venue is occupied' : 'Venue is available'
        ]);
    }

    // POST /timetable/create
    public function createTimetable(Request $request)
    {
        $timetable = Timetable::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Timetable created successfully',
            'data' => $timetable
        ], 201);
    }

    public function getTimetableByClassGroupId($classGroupId)
    {
        $timetables = Timetable::with(['venue', 'courseSubject.subject', 'courseSubject.course'])
            ->where(function ($query) use ($classGroupId) {
                $query->where('classGroupId', $classGroupId)
                    ->orWhereRaw("FIND_IN_SET(?, classGroupId) > 0", [$classGroupId]);
            })
            ->orderBy('classDate')
            ->orderBy('startTime')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $$timetables
        ]);
    }

    // GET /timetable/future/{userId}    For Appeals UI
    public function getFutureClasses($userId)
    {
        $studentProfile = $this->userService->getUser($userId); 
        
        $classGroupId = $studentProfile['classGroupId'];
        $today = Carbon::today()->format('Y-m-d');
        
        $classes = Timetable::with(['venue', 'courseSubject.subject'])
            // Lecture + Tutorial
            ->where(function ($query) use ($classGroupId) {
                $query->where('classGroupId', $classGroupId)
                    ->orWhereRaw("FIND_IN_SET(?, classGroupId) > 0", [$classGroupId]);
            })
            ->where('classDate', '>', $today)
            ->orderBy('classDate')
            ->orderBy('startTime')
            ->get();

        return response()->json([
            'data' => $classes,
            'count' => $classes->count()
        ]);
    }

}
