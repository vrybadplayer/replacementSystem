<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use Illuminate\Http\Request;

class ClassGroupController extends Controller
{
    // GET /api/classgroups
    public function getAllClassGroups()
    {
        $groups = ClassGroup::all();
        return response()->json(['data' => $groups]);
    }

    // GET /api/timetable/{id}
    public function getClassGroupById($id) {
        $record = ClassGroup::find($id);
        if (!$record) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json(['data' => $record]);
    }
}