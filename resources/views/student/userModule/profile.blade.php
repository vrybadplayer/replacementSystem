<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Replacement System</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 30px;
            color: #333;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }
        
        .profile-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .avatar {
            width: 80px;
            height: 80px;
            background: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
            margin: 0 auto 15px;
        }
        
        .profile-card h2 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #333;
        }
        
        .profile-divider {
            border-top: 1px solid #e5e7eb;
            margin: 20px 0;
            padding-top: 20px;
        }
        
        .profile-item {
            margin-bottom: 15px;
        }
        
        .profile-item-label {
            color: #999;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .profile-item-value {
            font-weight: 500;
            color: #333;
        }
        
        .edit-btn {
            width: 100%;
            padding: 10px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            margin-top: 20px;
        }
        
        .edit-btn:hover {
            background: #2563eb;
        }

        .btn-change-password {
            width: 100%;
            padding: 12px;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            font-size: 15px;
            transition: background 0.2s;
        }

        .btn-change-password:hover {
            background: #b91c1c;
        }
        
        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .section h3 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group p {
            padding: 10px;
            background: #f3f4f6;
            border-radius: 5px;
            color: #333;
        }
        
        .course-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        
        .course-item h4 {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .course-item-code {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .course-item-meta {
            color: #999;
            font-size: 13px;
        }
        
        .loading {
            color: #999;
            text-align: center;
            padding: 40px 20px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
        }
        
        .modal-content h2 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .modal-buttons button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .btn-cancel {
            background: #e5e7eb;
            color: #333;
        }
        
        .btn-cancel:hover {
            background: #d1d5db;
        }
        
        .btn-save {
            background: #3b82f6;
            color: white;
        }
        
        .btn-save:hover {
            background: #2563eb;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            margin: 0;
            color: #dc2626;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }

        .modal-close:hover {
            color: #333;
        }

        .error-text {
            display: block;
            color: #dc2626;
            font-size: 13px;
            margin-top: 5px;
        }

        .password-strength {
            display: block;
            font-size: 13px;
            margin-top: 5px;
        }

        .password-requirements {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .password-requirements p {
            margin: 0 0 10px 0;
            font-weight: 600;
            color: #333;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
            list-style: none;
        }

        .password-requirements li {
            padding: 5px 0;
            color: #666;
            position: relative;
            padding-left: 25px;
        }

        .password-requirements li:before {
            content: '✗';
            color: #dc2626;
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .password-requirements li.req-met:before {
            content: '✓';
            color: #10b981;
        }

        .alert {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-error p {
            margin: 5px 0;
            font-size: 13px;
        }
        
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <x-header title="My Profile">
        <div style="margin-left: auto; display: flex; align-items: center; gap: 1rem;">
            <span style="color: #666;">{{ Auth::user()->name }} (Student)</span>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">
                    Logout
                </button>
            </form>
        </div>
    </x-header>

    <div class="app-container">
        <x-student_sidebar />

        <main class="main-content">
            <div class="container">
                <h1>My Profile</h1>

                <div class="profile-grid">
                    <!-- Profile Card -->
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                            <h2>{{ Auth::user()->name }}</h2>
                        </div>

                        <div class="profile-divider">
                            <div class="profile-item">
                                <div class="profile-item-label">EMAIL</div>
                                <div class="profile-item-value">{{ Auth::user()->email }}</div>
                            </div>
                            
                            <div class="profile-item">
                                <div class="profile-item-label">ROLE</div>
                                <div class="profile-item-value">{{ Auth::user()->role }}</div>
                            </div>
                        </div>

                        <button class="edit-btn" onclick="showEditForm()">Edit Profile</button>
                    </div>

                    <!-- Main Content -->
                    <div>
                        <!-- Profile Info Section -->
                        <div class="section">
                            <h3>Profile Info</h3>
                            
                            <div class="form-group">
                                <label>Full Name</label>
                                <p>{{ Auth::user()->name }}</p>
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <p>{{ Auth::user()->email }}</p>
                            </div>

                            <div class="form-group">
                                <label>Account Type</label>
                                <p>{{ Auth::user()->role }}</p>
                            </div>
                        </div>

                        <!-- Courses Section -->
                        <div class="section">
                            <h3>Enrolled Courses</h3>
                            <div id="coursesList" class="loading">Loading courses...</div>
                        </div>

                        <!-- Change Password Section -->
                        <div class="section">
                            <h3>Change Password</h3>
                            <button class="btn-change-password" onclick="showChangePasswordForm()">🔐 Change Password</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Edit Profile</h2>

            <form onsubmit="submitEdit(event)">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" id="editName" value="{{ Auth::user()->name }}" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="editEmail" value="{{ Auth::user()->email }}" required>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeEditForm()">Cancel</button>
                    <button type="submit" class="btn-save">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🔐 Change Password</h2>
                <button type="button" class="modal-close" onclick="closeChangePasswordForm()">&times;</button>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('student.changePassword') }}" method="POST" onsubmit="return validateChangePassword(event)">
                @csrf
                
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="current_password" required placeholder="Enter your current password">
                    <span class="error-text" id="currentPasswordError"></span>
                </div>

                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="password" required placeholder="Enter new password">
                    <span class="password-strength" id="passwordStrength"></span>
                    <span class="error-text" id="newPasswordError"></span>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="password_confirmation" required placeholder="Confirm new password">
                    <span class="error-text" id="confirmPasswordError"></span>
                </div>

                <div class="password-requirements">
                    <p><strong>Password Requirements:</strong></p>
                    <ul>
                        <li id="req-length">At least 8 characters</li>
                        <li id="req-uppercase">At least one uppercase letter</li>
                        <li id="req-lowercase">At least one lowercase letter</li>
                        <li id="req-number">At least one number</li>
                    </ul>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeChangePasswordForm()">Cancel</button>
                    <button type="submit" class="btn-save">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadCourses();
        });

        function loadCourses() {
            fetch('/api/user/courses')
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    if (data.courses && data.courses.length > 0) {
                        data.courses.forEach(course => {
                            html += '<div class="course-item">';
                            html += '<h4>' + course.courseName + '</h4>';
                            html += '<p class="course-item-code">' + course.courseCode + '</p>';
                            html += '<p class="course-item-meta">Year ' + course.year + ' - Semester ' + course.semester + '</p>';
                            html += '</div>';
                        });
                    } else {
                        html = '<p style="color: #999;">No courses enrolled</p>';
                    }
                    document.getElementById('coursesList').innerHTML = html;
                })
                .catch(err => {
                    console.log(err);
                    document.getElementById('coursesList').innerHTML = '<p style="color: red;">Error loading courses</p>';
                });
        }

        function showEditForm() {
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditForm() {
            document.getElementById('editModal').style.display = 'none';
        }

        function submitEdit(e) {
            e.preventDefault();
            
            let name = document.getElementById('editName').value;
            let email = document.getElementById('editEmail').value;

            fetch('/api/user/profile/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    _token: '{{ csrf_token() }}'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Profile updated');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(err => {
                alert('Error updating profile');
                console.log(err);
            });
        }

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditForm();
            }
        });

        // Change Password Functions
        function showChangePasswordForm() {
            document.getElementById('changePasswordModal').style.display = 'flex';
        }

        function closeChangePasswordForm() {
            document.getElementById('changePasswordModal').style.display = 'none';
            document.getElementById('changePasswordModal').querySelector('form').reset();
            clearPasswordErrors();
        }

        function validateChangePassword(e) {
            clearPasswordErrors();
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            let isValid = true;

            if (!currentPassword) {
                document.getElementById('currentPasswordError').textContent = 'Current password is required';
                isValid = false;
            }

            if (newPassword.length < 8) {
                document.getElementById('newPasswordError').textContent = 'Password must be at least 8 characters';
                isValid = false;
            }

            if (!/[A-Z]/.test(newPassword)) {
                document.getElementById('newPasswordError').textContent = 'Password must contain at least one uppercase letter';
                isValid = false;
            }

            if (!/[a-z]/.test(newPassword)) {
                document.getElementById('newPasswordError').textContent = 'Password must contain at least one lowercase letter';
                isValid = false;
            }

            if (!/[0-9]/.test(newPassword)) {
                document.getElementById('newPasswordError').textContent = 'Password must contain at least one number';
                isValid = false;
            }

            if (newPassword !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
                isValid = false;
            }

            if (currentPassword === newPassword) {
                document.getElementById('newPasswordError').textContent = 'New password must be different from current password';
                isValid = false;
            }

            return isValid;
        }

        function clearPasswordErrors() {
            document.getElementById('currentPasswordError').textContent = '';
            document.getElementById('newPasswordError').textContent = '';
            document.getElementById('confirmPasswordError').textContent = '';
        }

        // Real-time password strength indicator
        document.getElementById('newPassword')?.addEventListener('input', function() {
            const password = this.value;
            updatePasswordRequirements(password);
        });

        function updatePasswordRequirements(password) {
            document.getElementById('req-length').className = password.length >= 8 ? 'req-met' : '';
            document.getElementById('req-uppercase').className = /[A-Z]/.test(password) ? 'req-met' : '';
            document.getElementById('req-lowercase').className = /[a-z]/.test(password) ? 'req-met' : '';
            document.getElementById('req-number').className = /[0-9]/.test(password) ? 'req-met' : '';
        }

        document.getElementById('changePasswordModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeChangePasswordForm();
            }
        });
    </script>
</body>
</html>
