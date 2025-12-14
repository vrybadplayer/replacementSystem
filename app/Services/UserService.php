<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Http\Request;

class UserService
{
    // ======================== TEMPORARY SERVICE FILE ========================
    // Make sure this points to your running server
    protected $baseUrl = 'http://127.0.0.1:8001/replacementSystem/public/api';

    public function getUser($userId)
    {
        // OLD WAY:
        // $response = Http::get("{$this->baseUrl}/users/{$userId}");

        // NEW WAY (Internal Dispatch):
        // 1. Create Request
        $request = Request::create("/api/users/{$userId}", 'GET');
        
        // 2. Dispatch
        $response = Route::dispatch($request);

        // 3. Decode
        $data = json_decode($response->getContent(), true);

        return $data;
    }
}
