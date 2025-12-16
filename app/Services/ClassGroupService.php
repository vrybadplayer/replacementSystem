<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClassGroupService
{
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    public function getAllClassGroups() {
        $response = Http::get("{$this->baseUrl}/classgroups");
        if ($response->successful()) {
            return $response->json()['data'] ?? [];
        }
        return [];
    }

    public function getClassGroupById(string $id) {
        $response = Http::get("{$this->baseUrl}/classgroups/{$id}");
        if ($response->successful()) {
            return $response->json()['data'] ?? null;
        }
        return null;
    }
}