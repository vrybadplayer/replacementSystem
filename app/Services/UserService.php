<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\CourseEnrollment;

class UserService
{
    // ======================== API BASE URL ========================
    // Make sure this points to your running server
    protected $baseUrl = 'http://127.0.0.1:8001/api';

    /**
     * Fetch user from API with fallback and error handling
     */
    public function getUser($userId)
    {
        try {
            Log::info('Fetching user from API', ['userId' => $userId]);
            $response = Http::timeout(5)->get("{$this->baseUrl}/users/{$userId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for getUser: " . $e->getMessage());
            
            // Fallback to database
            $user = User::find($userId);
            return $user ? $user->toArray() : [];
        }
    }

    /**
     * Fetch all users from API with fallback and error handling
     */
    public function getAllUsers()
    {
        try {
            Log::info('Fetching all users from API');
            $response = Http::timeout(5)->get("{$this->baseUrl}/users");

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for getAllUsers: " . $e->getMessage());
            
            // Fallback to database
            return User::all()->toArray();
        }
    }

    /**
     * Get user profile data with enrollments via API with fallback
     */
    public function getProfileData($userId)
    {
        try {
            Log::info('Fetching profile data from API', ['userId' => $userId]);
            $response = Http::timeout(5)->get("{$this->baseUrl}/users/{$userId}/profile");

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for getProfileData: " . $e->getMessage());
            
            // Fallback to database
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
    }

    /**
     * Get user enrolled courses via API with fallback
     */
    public function getEnrolledCourses($userId)
    {
        try {
            Log::info('Fetching enrolled courses from API', ['userId' => $userId]);
            $response = Http::timeout(5)->get("{$this->baseUrl}/users/{$userId}/courses");

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for getEnrolledCourses: " . $e->getMessage());
            
            // Fallback to database
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
    }

    /**
     * Get all users from database (with API and fallback)
     */
    public function getAllUsersFromDb()
    {
        try {
            Log::info('Fetching all users from API');
            $response = Http::timeout(5)->get("{$this->baseUrl}/users");

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for getAllUsersFromDb: " . $e->getMessage());
            
            // Fallback to database
            return User::all()->toArray();
        }
    }

    /**
     * Get user by ID with API and fallback to database
     */
    public function getUserById(string $userId)
    {
        try {
            Log::info('Fetching user by ID from API', ['userId' => $userId]);
            $response = Http::timeout(5)->get("{$this->baseUrl}/users/{$userId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for getUserById: " . $e->getMessage());
            
            // Fallback to database
            $user = User::find($userId);
            return $user ? $user->toArray() : null;
        }
    }

    /**
     * Create a new user with API and fallback
     */
    public function createUser(array $data)
    {
        try {
            Log::info('Creating user via API', ['data' => $data]);
            $response = Http::timeout(5)->post("{$this->baseUrl}/users", $data);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for createUser: " . $e->getMessage());
            
            // Fallback to database
            return User::create($data)->toArray();
        }
    }

    /**
     * Create or update a user with API and fallback
     */
    public function createOrUpdateUser(array $data, string $userId = null)
    {
        $id = $userId ?? $data['userId'];
        
        try {
            Log::info('Creating or updating user via API', ['userId' => $id]);
            $response = Http::timeout(5)->put("{$this->baseUrl}/users/{$id}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for createOrUpdateUser: " . $e->getMessage());
            
            // Fallback to database
            return User::updateOrCreate(
                ['userId' => $id],
                $data
            )->toArray();
        }
    }

    /**
     * Update a user with API and fallback
     */
    public function updateUser(string $userId, array $data)
    {
        try {
            Log::info('Updating user via API', ['userId' => $userId]);
            $response = Http::timeout(5)->put("{$this->baseUrl}/users/{$userId}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for updateUser: " . $e->getMessage());
            
            // Fallback to database
            $user = User::findOrFail($userId);
            $user->update($data);
            return $user->toArray();
        }
    }

    /**
     * Delete a user with API and fallback
     */
    public function deleteUser(string $userId)
    {
        try {
            Log::info('Deleting user via API', ['userId' => $userId]);
            $response = Http::timeout(5)->delete("{$this->baseUrl}/users/{$userId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("API call failed for deleteUser: " . $e->getMessage());
            
            // Fallback to database
            $user = User::findOrFail($userId);
            return $user->delete();
        }
    }

    /**
     * Get users formatted for views with API and fallback
     */
    public function getUsersForView()
    {
        try {
            Log::info('Fetching users for view from API');
            $response = Http::timeout(5)->get("{$this->baseUrl}/users");

            if ($response->successful()) {
                $users = $response->json();
                if (is_array($users)) {
                    return array_map(function ($user) {
                        return [
                            'id' => $user['userId'] ?? $user['id'] ?? null,
                            'name' => $user['name'] ?? null,
                            'email' => $user['email'] ?? null,
                            'role' => $user['role'] ?? null,
                            'classGroup' => $user['classGroupId'] ?? $user['classGroup'] ?? null,
                        ];
                    }, $users);
                }
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for getUsersForView: " . $e->getMessage());
            
            // Fallback to database
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
    }

    /**
     * Get user profile formatted for view with API and fallback
     */
    public function getUserProfileForView($userId)
    {
        try {
            Log::info('Fetching user profile for view from API', ['userId' => $userId]);
            $response = Http::timeout(5)->get("{$this->baseUrl}/users/{$userId}");

            if ($response->successful()) {
                $user = $response->json();
                return [
                    'id' => $user['userId'] ?? $user['id'] ?? null,
                    'name' => $user['name'] ?? null,
                    'email' => $user['email'] ?? null,
                    'role' => $user['role'] ?? null,
                    'classGroup' => $user['classGroupId'] ?? $user['classGroup'] ?? null,
                ];
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for getUserProfileForView: " . $e->getMessage());
            
            // Fallback to database
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
    }

    /**
     * Update user profile (name and email) with API and fallback
     */
    public function updateProfile($userId, array $data)
    {
        try {
            Log::info('Updating user profile via API', ['userId' => $userId]);
            $response = Http::timeout(5)->put("{$this->baseUrl}/users/{$userId}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('API returned unsuccessful response: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning("API call failed for updateProfile: " . $e->getMessage());
            
            // Fallback to database
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

            return $user->toArray();
        }
    }
}
