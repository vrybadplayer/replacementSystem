<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function showProfile()
    {
        $user = Auth::user();
        return view('student.userModule.profile', ['user' => $user]);
    }

    public function getProfileData()
    {
        $user = Auth::user();
        
        $enrollments = CourseEnrollment::where('userID', $user->userId)->with('course')->get();

        return response()->json([
            'user' => [
                'userId' => $user->userId,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'enrollments' => $enrollments,
            'count' => count($enrollments),
        ]);
    }

    public function getEnrolledCourses()
    {
        $user = Auth::user();
        
        $courses = CourseEnrollment::where('userID', $user->userId)
            ->with('course')
            ->get();

        $result = [];
        foreach ($courses as $enrollment) {
            $result[] = [
                'enrollmentID' => $enrollment->enrollmentID,
                'courseID' => $enrollment->courseID,
                'courseName' => $enrollment->course->courseName,
                'courseCode' => $enrollment->course->courseCode,
                'year' => $enrollment->year,
                'semester' => $enrollment->semester,
            ];
        }

        return response()->json(['courses' => $result]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $name = $request->input('name');
        $email = $request->input('email');

        if (empty($name) || empty($email)) {
            return response()->json(['error' => 'Name and email required'], 400);
        }

        $user->name = $name;
        $user->email = $email;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Updated']);
    }
}

