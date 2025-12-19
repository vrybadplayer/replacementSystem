<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\ReplacementAppeal;
use Illuminate\Support\Facades\Http;


class ReplacementService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    // get timetable by ID
    public function getTimetableByIdFromApi($timetableId)
    {
        $url = "{$this->baseUrl}/timetable/{$timetableId}";
        $response = Http::timeout(5)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $classData = $data['data'] ?? null;

            if ($classData && isset($classData['timetableId'])) {
                return Timetable::with(['subject', 'venue', 'classGroup'])
                    ->find($classData['timetableId']);
            }
        }
        
        return null;
    }

    // get available classes
    public function getAvailableClassesFromApi()
    {
        $url = "{$this->baseUrl}/timetable";
        $response = Http::timeout(5)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $classesData = collect($data['data'] ?? []);

            $futureClasses = $classesData->filter(function ($class) {
                $classDate = is_array($class) ? ($class['classDate'] ?? null) : ($class->classDate ?? null);
                return $classDate && $classDate >= now()->format('Y-m-d');
            });

            if ($futureClasses->isNotEmpty()) {
                $classIds = $futureClasses->pluck('timetableId')->toArray();
                return Timetable::with(['subject', 'venue', 'classGroup'])
                    ->whereIn('timetableId', $classIds)
                    ->where('classDate', '>=', now()->format('Y-m-d'))
                    ->orderBy('classDate')
                    ->orderBy('startTime')
                    ->get();
            }
        }
        
        return collect([]);
    }

    // get user appeals
    public function getUserAppealsFromApi($userId)
    {
        $url = "{$this->baseUrl}/replacement/appeals/user/{$userId}";
        $response = Http::timeout(5)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $appealsData = $data['data'] ?? [];

            return collect($appealsData)->map(function ($appeal) {
                return $this->arrayToObject($appeal);
            });
        }
        
        return collect([]);
    }

    // get single appeal
    public function getAppealFromApi($appealId)
    {
        $response = Http::get($this->baseUrl . '/replacement/appeals/' . $appealId);

        if ($response->successful() && $response->json('success')) {
            return $this->arrayToObject($response->json('data'));
        }

        return null;
    }

    // get all appeals
    public function getAllAppealsFromApi()
    {
        $url = "{$this->baseUrl}/replacement/appeals";
        $response = Http::timeout(5)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $appealsData = $data['data'] ?? [];

            return collect($appealsData)->map(function ($appeal) {
                return $this->arrayToObject($appeal);
            });
        }
        
        return collect([]);
    }

    // convert array to object
    private function arrayToObject($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $object = new \stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $object->$key = $this->arrayToObject($value);
            } else {
                $object->$key = $value;
            }
        }
        return $object;
    }

    // get statistics
    public function getStatisticsFromApi()
    {
        $url = "{$this->baseUrl}/replacement/statistics";
        $response = Http::timeout(5)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $statsData = $data['data'] ?? [];

            return [
                'total' => $statsData['total_appeals'] ?? 0,
                'pending' => $statsData['pending_appeals'] ?? 0,
                'approved' => $statsData['approved_appeals'] ?? 0,
                'rejected' => $statsData['rejected_appeals'] ?? 0
            ];
        }
        
        return [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0
        ];
    }

    public function getValidReplacementOptions($filterData)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        
        $response = Http::get("{$this->baseUrl}/replacement/validOptions", $filterData);
        
        if ($response->successful()) {
            return $response->json()['data'] ?? [];
        }

        return [];
    }
}
