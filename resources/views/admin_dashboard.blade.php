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

    <div class="app-container">
        <x-admin_sidebar />

        <main class="main-content">
            <div class="home-content">
     
                <div class="welcome-section">
                    <h2>Welcome to Admin Dashboard</h2>
                    <p>Manage class replacements, timetables, and system administration</p>
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

                    <a href="{{ route('student.replacementOptions') }}" class="action-card">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                        </div>
                        <h3>Replacement Options</h3>
                        <p>Find available replacement classes</p>
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

                    <a href="{{ route('replacement.appeal') }}" class="action-card">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3>Replacement Appeal</h3>
                        <p>Submit an appeal for replacement</p>
                    </a>
                </div>

            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>

