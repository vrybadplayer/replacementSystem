<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;

class ClassGroupController extends Controller
{
    public function getAllClassGroups()
    {
        $groups = ClassGroup::all();
        return response()->json(['data' => $groups]);
    }

    public function getClassGroupById($id) {
        $record = ClassGroup::find($id);
        if (!$record) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json(['data' => $record]);
    }
}