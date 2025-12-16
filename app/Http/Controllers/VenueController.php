<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    // GET /api/venues
    public function getAllVenue() {
        $venues = Venue::all();
        return response()->json(['data' => $venues]);
    }

    // GET /api/venue/{id}
    public function getVenueById($id) {
        $record = Venue::find($id);
        if (!$record) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json(['data' => $record]);
    }
}