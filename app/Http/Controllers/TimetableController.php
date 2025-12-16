<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    // GET /api/timetable
    public function getAllTimetableRecords() {
        $timetable = Timetable::all();
        return response()->json(['data' => $timetable]);
    }

    // GET /api/timetable/{id}
    public function getTimetableById($id) {
        $record = Timetable::find($id);
        if (!$record) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json(['data' => $record]);
    }
}