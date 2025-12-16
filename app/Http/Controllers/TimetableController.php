<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use Illuminate\Http\Request;
use App\Services\TimetableService;

class TimetableController extends Controller
{

    protected $timetableService;

    public function __construct(TimetableService $timetableService)
    {
        $this->timetableService = $timetableService;
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

    // GET /timetable/schedule
    public function getStudentWeekSchedule(Request $request)
    {
        $classGroupId = $request->query('classGroupId');
        $startOfWeek = $request->query('startOfWeek');
        $endOfWeek = $request->query('endOfWeek');

        $classes = Timetable::with(['venue', 'courseSubject'])
            ->where(function ($query) use ($classGroupId) {
                $query->where('classGroupId', $classGroupId)
                    ->orWhereRaw("FIND_IN_SET(?, classGroupId) > 0", [$classGroupId]);
            })
            ->whereBetween('classDate', [$startOfWeek, $endOfWeek])
            ->orderBy('classDate')
            ->orderBy('startTime')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classes,
            'count' => $classes->count()
        ]);
    }
}
