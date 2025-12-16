<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VenueService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    public function getAllVenues() {
        $response = Http::get("{$this->baseUrl}/venues");
        if ($response->successful()) {
            return $response->json()['data'] ?? [];
        }
        return [];
    }

    public function getVenueById(string $id) {
        $response = Http::get("{$this->baseUrl}/venues/{$id}");
        if ($response->successful()) {
            return $response->json()['data'] ?? null;
        }
        return null;
    }
}