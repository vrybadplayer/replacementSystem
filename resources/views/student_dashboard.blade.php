<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Replacement System</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/admin_dashboard.css') }}">
</head>
<body>
    <x-header title="Student Dashboard">
        <!-- Add logout button to header -->
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
            <div class="home-content">
     
                <div class="welcome-section">
                    <h2>Welcome, {{ Auth::user()->name }}!</h2>
                    <p>View your timetable and manage class replacements</p>
                </div>

                <div class="quick-actions">
                    <a href="{{ route('student.timetable') }}" class="action-card">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3>View Timetable</h3>
                        <p>Check your class schedule and timetable</p>
                    </a>

                    <a href="{{ route('student.selectTimeslot') }}" class="action-card">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3>Select Timeslot</h3>
                        <p>Choose your preferred class timeslot</p>
                    </a>

                    <a href="{{ route('replacement.appeal') }}" class="action-card">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3>Replacement Appeal</h3>
                        <p>Submit an appeal for replacement</p>
                    </a>

                    <a href="{{ route('replacement.myAppeals') }}" class="action-card">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <h3>My Appeals</h3>
                        <p>View your replacement appeal history</p>
                    </a>

                    <a href="{{ route('student.profile') }}" class="action-card">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3>My Profile</h3>
                        <p>View and manage your profile</p>
                    </a>
                </div>

            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>