<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course List - Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/course_list.css') }}">
</head>
<body>
    <x-header title="Admin Dashboard" />

    <div class="app-container">
        <x-admin_sidebar />

        <main class="main-content">
            <div class="list-container">
                <div class="list-header">
                    <h2>Course List</h2>
                    <a href="#" class="btn-add">+ Add New Course</a>
                </div>

                <div class="search-container">
                    <div class="search-box">
                        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" class="search-input" placeholder="Search courses by code, name, or department...">
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Credits</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                        <tr>
                            <td><strong>{{ $course['code'] }}</strong></td>
                            <td>{{ $course['name'] }}</td>
                            <td>{{ $course['credits'] }}</td>
                            <td>{{ $course['department'] }}</td>
                            <td>
                                <span class="status-badge status-{{ $course['status'] }}">
                                    {{ ucfirst($course['status']) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="#" class="btn-edit">Edit</a>
                                    <a href="#" class="btn-delete">Delete</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #6b7280;">
                                No courses found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>

