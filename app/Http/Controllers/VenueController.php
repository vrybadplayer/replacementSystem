<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Venue;

class VenueController extends Controller
{

    public function getAllVenues() {
        $venues = Venue::all();
        return response()->json(['data' => $venues]);
    }

    public function getVenueById($id) {
        $record = Venue::find($id);
        if (!$record) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json(['data' => $record]);
    }
}