<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        
        .app-container {
            display: flex;
        }
        
        .main-content {
            flex: 1;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 30px;
            color: #333;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        button, .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-warning {
            background: #fbbf24;
            color: #78350f;
        }

        .btn-warning:hover {
            background: #f59e0b;
            color: #451a03;
        }

        .btn-reset-password {
            background: #dc2626;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
        }

        .btn-reset-password:hover {
            background: #b91c1c;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .user-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-row {
            margin-bottom: 10px;
            color: #666;
        }
        
        .info-label {
            font-weight: 600;
            color: #333;
        }

        /* Password Reset Section */
        .password-reset-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-top: 30px;
            border: 2px solid #fee2e2;
            border-left: 5px solid #dc2626;
        }

        .password-reset-section h3 {
            color: #dc2626;
            margin: 0 0 10px 0;
            font-size: 1.2rem;
        }

        .section-description {
            color: #666;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .reset-info {
            background: #fef2f2;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-icon {
            font-size: 1.3rem;
            margin-right: 12px;
        }

        .info-item div {
            color: #333;
            font-size: 0.95rem;
        }

        .info-item strong {
            display: inline-block;
            min-width: 60px;
            color: #666;
        }

        .reset-help-text {
            margin-top: 12px;
            font-size: 0.85rem;
            color: #666;
            font-style: italic;
        }

    </style>
</head>
<body>
    <div class="app-container">
        <x-admin_sidebar />
        
        <div class="main-content">
            <x-header title="Edit User" />
            
            <div class="container">
                <h1>Edit User</h1>
                
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif
                
                <div class="user-info">
                    <div class="info-row">
                        <span class="info-label">User ID:</span> {{ $user->userId }}
                    </div>
                </div>
                
                <form action="{{ route('admin.user.update', $user->userId) }}" method="POST">
                    @csrf
                    @method('POST')
                    
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="{{ $user->name }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ $user->email }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="lecturer" {{ $user->role === 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>

                <!-- Password Reset Section -->
                <div class="password-reset-section">
                    <h3>Reset User Password</h3>
                    <p class="section-description">Generate a new temporary password for this user. An email notification will be sent automatically.</p>
                    
                    <form method="POST" action="{{ route('admin.user.resetPassword', $user->userId) }}" onsubmit="return confirm('Are you sure you want to reset the password for ' + '{{ $user->name }}' + '?');">
                        @csrf
                        <div class="reset-info">
                            <div class="info-item">
                                <span class="info-icon"></span>
                                <div>
                                    <strong>User:</strong> {{ $user->name }}
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="info-icon"></span>
                                <div>
                                    <strong>Email:</strong> {{ $user->email }}
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-reset-password">Reset Password</button>
                        <p class="reset-help-text">The user will receive the new password via email and can change it after logging in.</p>
                    </form>
                </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
