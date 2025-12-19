<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use Illuminate\Http\Request;
use App\Services\TimetableService;
use App\Services\UserService;

class TimetableController extends Controller
{

    protected $timetableService;
    protected $userService;

    public function __construct(TimetableService $timetableService, UserService $userService)
    {
        $this->timetableService = $timetableService;
        $this->userService = $userService;
    }

    public function getAllTimetableRecords()
    {
        $timetable = Timetable::all();
        return response()->json(['data' => $timetable]);
    }

    public function getTimetableById($id)
    {
        $record = Timetable::find($id);
        if (!$record) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json(['data' => $record]);
    }

    public function getOccupiedSlots(Request $request)
    {
        $day = $request->query('day');
        $venueId = $request->query('venueId');

        return Timetable::where('dayOfWeek', $day)
            ->where('venueId', $venueId)
            ->select('startTime', 'endTime')
            ->get();
    }

    public function claskCheck(Request $request)
    {

        $day = $request->query('day');
        $venueId = $request->query('venueId');
        $startTime = $request->query('startTime');
        $endTime = $request->query('endTime');

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

    public function searchTimetable(Request $request)
    {
        try {
            $query = Timetable::with(['venue', 'courseSubject']);

            if ($request->has('classDate')) {
                $query->where('classDate', $request->classDate);
            }

            if ($request->has('startTime') && $request->has('endTime')) {
                $query->where('startTime', '<=', $request->startTime)
                    ->where('endTime', '>=', $request->endTime);
            }

            if ($request->has('classGroupId')) {
                $classGroupId = $request->classGroupId;
                $query->where(function ($q) use ($classGroupId) {
                    $q->where('classGroupId', $classGroupId)
                        ->orWhereRaw('FIND_IN_SET(?, classGroupId) > 0', [$classGroupId]);
                });
            }

            $timetable = $query->get();

            return response()->json([
                'success' => true,
                'data' => $timetable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to search timetable.'
            ], 500);
        }
    }
}
