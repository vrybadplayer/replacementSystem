<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\CourseEnrollment;

class UserService
{
    // ======================== API BASE URL ========================
    // Make sure this points to your running server
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    /**
     * Fetch user from API
     */
    public function getUser($userId)
    {
        $response = Http::get("{$this->baseUrl}/users/{$userId}");

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Fetch all users from API
     */
    public function getAllUsers()
    {
        $response = Http::get("{$this->baseUrl}/users");

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Get user profile data with enrollments
     */
    public function getProfileData($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        $enrollments = CourseEnrollment::where('userID', $user->userId)->with('course')->get();

        return [
            'user' => [
                'userId' => $user->userId,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'enrollments' => $enrollments,
            'count' => count($enrollments),
        ];
    }

    /**
     * Get user enrolled courses
     */
    public function getEnrolledCourses($userId)
    {
        $courses = CourseEnrollment::where('userID', $userId)
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

        return $result;
    }

    /**
     * Get all users from database
     */
    public function getAllUsersFromDb()
    {
        return User::all();
    }

    /**
     * Get user by ID from database
     */
    public function getUserById(string $userId)
    {
        return User::find($userId);
    }

    /**
     * Create a new user
     */
    public function createUser(array $data)
    {
        return User::create($data);
    }

    /**
     * Create or update a user
     */
    public function createOrUpdateUser(array $data, string $userId = null)
    {
        return User::updateOrCreate(
            ['userId' => $userId ?? $data['userId']],
            $data
        );
    }

    /**
     * Update a user
     */
    public function updateUser(string $userId, array $data)
    {
        $user = User::findOrFail($userId);
        $user->update($data);
        return $user;
    }

    /**
     * Delete a user
     */
    public function deleteUser(string $userId)
    {
        $user = User::findOrFail($userId);
        return $user->delete();
    }

    /**
     * Get users formatted for views
     */
    public function getUsersForView()
    {
        return User::all()->map(function ($user) {
            return [
                'id' => $user->userId,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'classGroup' => $user->classGroupId,
            ];
        })->toArray();
    }

    /**
     * Get user profile formatted for view
     */
    public function getUserProfileForView($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        return [
            'id' => $user->userId,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'classGroup' => $user->classGroupId,
        ];
    }

    /**
     * Update user profile (name and email)
     */
    public function updateProfile($userId, array $data)
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception('User not found');
        }

        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        $user->save();

        return $user;
    }
}
