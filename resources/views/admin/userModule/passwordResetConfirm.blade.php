<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Confirmation - Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/passwordResetConfirm.css') }}">
</head>
<body>
    <x-header title="Admin Dashboard" />

    <div class="app-container">
        <x-admin_sidebar />

        <main class="main-content">
            <div class="reset-container">
                <div class="reset-success-card">
                    <div class="success-icon">✓</div>
                    <h2>Password Reset Successful!</h2>
                    <p class="reset-message">The password has been successfully reset and an email has been sent to the user.</p>

                    <div class="user-details">
                        <div class="detail-row">
                            <span class="detail-label">User ID:</span>
                            <span class="detail-value">{{ $user->userId }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">User Name:</span>
                            <span class="detail-value">{{ $user->name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email Address:</span>
                            <span class="detail-value">{{ $email }}</span>
                        </div>
                    </div>

                    <div class="password-section">
                        <label class="password-label">New Temporary Password</label>
                        <div class="password-display">
                            <input type="text" id="passwordInput" class="password-input" value="{{ $newPassword }}" readonly>
                            <button class="copy-btn" onclick="copyPassword()">📋 Copy</button>
                        </div>
                        <p class="password-hint">Click "Copy" to copy the password to clipboard</p>
                    </div>

                    <div class="alert alert-info">
                        <strong>Note:</strong>
                        <ul>
                            <li>A password reset email has been automatically sent to the user</li>
                            <li>The user should change this password upon first login</li>
                            <li>Keep this page or the email for reference</li>
                        </ul>
                    </div>

                    <div class="action-buttons">
                        <a href="{{ route('admin.user.show', $user->userId) }}" class="btn btn-primary">View User Profile</a>
                        <a href="{{ route('admin.user.index') }}" class="btn btn-secondary">Back to User List</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function copyPassword() {
            const passwordInput = document.getElementById('passwordInput');
            passwordInput.select();
            document.execCommand('copy');
            
            const copyBtn = document.querySelector('.copy-btn');
            const originalText = copyBtn.textContent;
            copyBtn.textContent = '✓ Copied!';
            copyBtn.style.backgroundColor = '#10b981';
            
            setTimeout(() => {
                copyBtn.textContent = originalText;
                copyBtn.style.backgroundColor = '';
            }, 2000);
        }
    </script>
</body>
</html>
