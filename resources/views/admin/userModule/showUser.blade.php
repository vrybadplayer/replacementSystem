<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/profile.css') }}">
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
            </div>
        </main>
    </div>
</body>
</html>
