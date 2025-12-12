<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Replacement System</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/admin_dashboard.css') }}">
</head>
<body>
    <x-header title="Admin Dashboard" />
    <div style="margin-left: auto; display: flex; align-items: center; gap: 1rem;">
            <span style="color: #666;">{{ Auth::user()->name }} (Admin)</span>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">
                    Logout
                </button>
            </form>
        </div>

    <div class="app-container">
        <x-admin_sidebar />

        <main class="main-content">
            <div class="home-content">
     
                <div class="welcome-section">
                    <h2>Welcome to Admin Dashboard</h2>
                    <p>Manage class replacements, timetables, and system administration</p>
                </div>

                <div class="quick-actions">
                    <a href="{{ route('admin.subjectAssignment') }}" class="action-card">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3>Subject Assignment</h3>
                        <p>Admin: Manage subject assignments</p>
                    </a>

                    <a href="{{ route('admin.timeslotManagement') }}" class="action-card">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3>Timeslot Management</h3>
                        <p>Admin: Manage available timeslots</p>
                    </a>

                    <a href="{{ route('admin.replacement.appeals') }}" class="action-card">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <h3>Manage Appeals (Admin)</h3>
                        <p>Review and approve/reject appeals</p>
                    </a>

                    <a href="{{ route('replacement.appeal') }}" class="action-card">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <h3>Submit Appeal (Student)</h3>
                        <p>Request a replacement class</p>
                    </a>
                </div>

            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>

