<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle authentication for API requests
     * Only allows Bearer token authentication (for internal services)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization', ''));
        
        if ($request->header('X-Internal-Request') === 'true' && $this->validateToken($token)) {
            return $next($request);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated. Please provide valid Bearer token.',
        ], 401);
    }
    
    /**
     * Validate the internal API token
     */
    private function validateToken(string $token): bool
    {
        $decoded = base64_decode($token, true);
        if (!$decoded) {
            return false;
        }
        
        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            return false;
        }
        
        [$secret, $timestamp, $type] = $parts;
        $validSecret = env('INTERNAL_API_SECRET', 'your-secret-key-change-this');
        
        return $secret === $validSecret 
            && $type === 'internal' 
            && (time() - (int)$timestamp) <= 3600;
    }
}

