<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/showUser.css') }}">
</head>
<body>
    <x-header title="Admin Dashboard" />

    <div class="app-container">
        <x-admin_sidebar />

        <main class="main-content">
            <div class="profile-container">
                <div class="profile-header">
                    <h2>User Profile</h2>
                    <div class="profile-actions">
                        <a href="{{ route('admin.user.edit', $user->userId) }}" class="btn-action">Edit</a>
                        <form method="POST" action="{{ route('admin.user.resetPassword', $user->userId) }}" style="display: inline;" onsubmit="return confirm('Reset password for this user?');">
                            @csrf
                            <button type="submit" class="btn-action btn-warning">Reset Password</button>
                        </form>
                        <a href="{{ route('admin.user.index') }}" class="btn-action btn-secondary">Back</a>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="profile-info">
                        <div class="info-group">
                            <label>User ID:</label>
                            <span>{{ $user->userId }}</span>
                        </div>
                        <div class="info-group">
                            <label>Name:</label>
                            <span>{{ $user->name }}</span>
                        </div>
                        <div class="info-group">
                            <label>Email:</label>
                            <span>{{ $user->email }}</span>
                        </div>
                        <div class="info-group">
                            <label>Role:</label>
                            <span><span class="role-badge role-{{ strtolower($user->role) }}">{{ $user->role }}</span></span>
                        </div>
                        <div class="info-group">
                            <label>Status:</label>
                            <span>{{ ucfirst($user->status) }}</span>
                        </div>
                    </div>
                </div>

                @if($enrolledCourses && count($enrolledCourses) > 0)
                <div class="profile-card">
                    <h3 style="margin-bottom: 20px; font-weight: bold;">Enrolled Courses</h3>
                    <div style="display: grid; gap: 15px;">
                        @foreach($enrolledCourses as $course)
                        <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                            <div style="font-weight: bold; margin-bottom: 5px;">{{ $course['courseName'] }}</div>
                            <div style="color: #666; font-size: 14px; margin-bottom: 5px;">{{ $course['courseCode'] }}</div>
                            <div style="color: #999; font-size: 13px;">Year: {{ $course['year'] }} | Semester: {{ $course['semester'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($subjects && count($subjects) > 0)
                <div class="profile-card">
                    <h3 style="margin-bottom: 20px; font-weight: bold;">Subjects</h3>
                    <div style="display: grid; gap: 15px;">
                        @foreach($subjects as $subject)
                        <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                            <div style="font-weight: bold; margin-bottom: 5px;">{{ $subject['subjectName'] }}</div>
                            <div style="color: #666; font-size: 14px; margin-bottom: 5px;">Code: {{ $subject['subjectCode'] }}</div>
                            <div style="color: #999; font-size: 13px;">Credit Hours: {{ $subject['creditHours'] }} | Category: {{ $subject['category'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </main>
    </div>
</body>
</html>
