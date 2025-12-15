<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        if ($request->user()->role !== $role) {
            // Store intended URL for the controller to handle
            session(['url.intended' => $request->fullUrl()]);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect('/login')->with('error', 'You do not have permission to access this resource. Please log in with the correct account.');
        }

        return $next($request);
    }
}
