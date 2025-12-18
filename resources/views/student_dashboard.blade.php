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

                @if(!$hasEnrollments)
                <div style="margin-bottom: 30px; padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; display: flex; align-items: center; gap: 15px;">
                    <svg style="width: 30px; height: 30px; color: #ff6b6b; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 style="margin: 0 0 5px 0; font-weight: 600; color: #333;">No Courses Enrolled</h3>
                        <p style="margin: 0; color: #666; font-size: 14px;">You haven't enrolled in any courses yet. Enroll now to access your courses and timetable.</p>
                    </div>
                </div>
                @endif

                <div class="quick-actions">
                    @if(!$hasEnrollments)
                    <div class="action-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; cursor: pointer;" onclick="document.getElementById('enrollModal').style.display='flex'">
                        <div class="action-card-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <h3>Enroll in Course</h3>
                        <p>Select and enroll in a course to get started</p>
                    </div>
                    @endif

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

    <!-- Enrollment Modal -->
    <div id="enrollModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
        <div style="background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 24px; color: #333;">Enroll in a Course</h2>
                <button onclick="document.getElementById('enrollModal').style.display='none'" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
            </div>

            @if($availableCourses && count($availableCourses) > 0)
            <form method="POST" action="{{ route('student.enrollCourse') }}" style="display: none; margin-top: 20px;" id="enrollForm">
                @csrf
                <input type="hidden" name="courseId" id="courseId">
                <button type="submit" style="display: none;" id="submitEnrollBtn"></button>
            </form>

            <div style="max-height: 400px; overflow-y: auto;">
                @foreach($availableCourses as $course)
                <div style="padding: 15px; border: 1px solid #e0e0e0; border-radius: 6px; margin-bottom: 10px; cursor: pointer; transition: all 0.3s;" 
                     onmouseover="this.style.background='#f5f5f5'; this.style.borderColor='#667eea';" 
                     onmouseout="this.style.background='white'; this.style.borderColor='#e0e0e0';"
                     onclick="enrollCourse('{{ $course->courseId }}', '{{ $course->courseName }}')">
                    <h4 style="margin: 0 0 5px 0; color: #333; font-weight: 600;">{{ $course->courseCode }} - {{ $course->courseName }}</h4>
                    <p style="margin: 0; color: #666; font-size: 13px;">{{ $course->description ?? 'No description available' }}</p>
                </div>
                @endforeach
            </div>
            @else
            <p style="color: #666; text-align: center; padding: 20px;">No courses available for enrollment.</p>
            @endif
        </div>
    </div>

    <script>
        function enrollCourse(courseId, courseName) {
            if (confirm(`Are you sure you want to enroll in ${courseName}?`)) {
                document.getElementById('courseId').value = courseId;
                document.getElementById('enrollForm').submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('enrollModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    </script>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>