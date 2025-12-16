<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TimetableService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    public function getAllTimetableRecords()
    {
        $response = Http::get("{$this->baseUrl}/timetable");
        if ($response->successful()) {
            return $response->json()['data'] ?? [];
        }
        return [];
    }
    
    public function getTimetableById(string $id)
    {
        $response = Http::get("{$this->baseUrl}/timetable/{$id}");
        if ($response->successful()) {
            return $response->json()['data'] ?? null;
        }
        return null;
    }
}