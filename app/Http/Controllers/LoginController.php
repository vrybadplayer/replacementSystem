<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // If already authenticated with wrong role, logout and show login form
        if (Auth::check()) {
            // Check if user is trying to access a restricted route
            $intended = session('url.intended');
            
            if ($intended) {
                // Check if user has permission for intended route
                if (str_contains($intended, '/admin') && !Auth::user()->isAdmin()) {
                    Auth::logout();
                    return view('login')->with('error', 'Your account does not have access to the admin panel.');
                }
                if (str_contains($intended, '/student') && !Auth::user()->isStudent()) {
                    Auth::logout();
                    return view('login')->with('error', 'Your account does not have access to the student dashboard.');
                }
            }
            
            // User has appropriate role, redirect to dashboard
            if (Auth::user()->isAdmin()) {
                return redirect('/admin/dashboard');
            }
            if (Auth::user()->isStudent()) {
                return redirect('/student/dashboard');
            }
        }
        
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');
        $clientIp = $request->ip();

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Log successful authentication
            \Log::info('Authentication successful', [
                'user_id' => $user->userId,
                'email' => $user->email,
                'role' => $user->role,
                'ip_address' => $clientIp,
                'remember_me' => $remember,
                'timestamp' => now()
            ]);

            // Check if user is banned
            if ($user->status === 'banned') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Log banned user attempt
                \Log::warning('Authentication blocked - user banned', [
                    'user_id' => $user->userId,
                    'email' => $user->email,
                    'ip_address' => $clientIp,
                    'timestamp' => now()
                ]);
                
                throw ValidationException::withMessages([
                    'email' => __('Your account has been banned. Please contact an administrator.'),
                ]);
            }

            // Redirect based on role
            if ($user->isAdmin()) {
                return redirect()->intended('/admin/dashboard');
            }

            if ($user->isStudent()) {
                return redirect()->intended('/student/dashboard');
            }

            if ($user->isLecturer()) {
                // Redirect lecturers to login for now (no lecturer dashboard yet)
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Log lecturer access attempt
                \Log::warning('Authentication blocked - lecturer role not allowed', [
                    'user_id' => $user->userId,
                    'email' => $user->email,
                    'role' => 'lecturer',
                    'ip_address' => $clientIp,
                    'timestamp' => now()
                ]);
                
                throw ValidationException::withMessages([
                    'email' => __('Lecturer accounts do not have access yet.'),
                ]);
            }

            // Invalid role - log out and redirect to login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            \Log::warning('Authentication blocked - invalid role', [
                'user_id' => $user->userId,
                'email' => $user->email,
                'role' => $user->role,
                'ip_address' => $clientIp,
                'timestamp' => now()
            ]);
            
            throw ValidationException::withMessages([
                'email' => __('Your account role is invalid. Please contact an administrator.'),
            ]);
        }

        // Log failed authentication attempt
        \Log::warning('Authentication failed - invalid credentials', [
            'email' => $credentials['email'],
            'ip_address' => $clientIp,
            'timestamp' => now()
        ]);

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $clientIp = $request->ip();

        // Log logout event
        if ($user) {
            \Log::info('User logged out', [
                'user_id' => $user->userId,
                'email' => $user->email,
                'role' => $user->role,
                'ip_address' => $clientIp,
                'timestamp' => now()
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}