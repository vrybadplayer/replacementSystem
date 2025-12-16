<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use APp\Models\Timetable;

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

    public function getTimetableById($id)
    {
        $response = Http::get("{$this->baseUrl}/timetable/{$id}");
        if ($response->successful()) {
            return $response->json()['data'] ?? null;
        }
        return null;
    }

    public function getTimetableByClassGroupId($classGroupId)
    {
        $response = Http::get("{$this->baseUrl}/timetable/{$classGroupId}");
        if ($response->successful()) {
            return $response->json()['data'] ?? null;
        }
        return null;
    }

    public function getOccupiedSlots($day, $venueId)
    {
        $response = Http::get($this->baseUrl . '/occupiedSlots', [
            'day' => $day,
            'venueId' => $venueId
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return collect($data['data'] ?? []);
        }

        return collect([]);
    }

    public function clashCheck($day, $venueId, $startTime, $endTime)
    {
        $response = Http::get($this->baseUrl . '/clashCheck', [
            'day' => $day,
            'venueId' => $venueId,
            'startTime' => $startTime,
            'endTime' => $endTime
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['isOccupied'] ?? false;
        }

        return false;
    }

    public function createTimetable($data)
    {
        $response = Http::post($this->baseUrl . '/timetable/create', $data);

        if ($response->successful()) {
            $result = $response->json();
            return $result['success'] ? $result['data'] : null;
        }

        return null;
    }

    public function getStudentSchedule($classGroupId, $startOfWeek, $endOfWeek)
    {

        //dd($classGroupId);
        //dd($startOfWeek);
        //dd($endOfWeek);

        $response = Http::get($this->baseUrl . '/timetable/schedule', [
            'classGroupId' => $classGroupId,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return collect($data['data'] ?? []);
        }

        return collect([]);
    }
}
