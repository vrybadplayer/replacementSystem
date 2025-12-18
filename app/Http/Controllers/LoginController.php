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

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user is banned
            if ($user->status === 'banned') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
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
                
                throw ValidationException::withMessages([
                    'email' => __('Lecturer accounts do not have access yet.'),
                ]);
            }

            // Invalid role - log out and redirect to login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            \Log::warning('Invalid user role during login', ['role' => $user->role, 'email' => $user->email]);
            
            throw ValidationException::withMessages([
                'email' => __('Your account role is invalid. Please contact an administrator.'),
            ]);
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}