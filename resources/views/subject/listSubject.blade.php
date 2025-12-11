<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject List - Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/subject_list.css') }}">
</head>
<body>
    <x-header title="Admin Dashboard" />

    <div class="app-container">
        <x-admin_sidebar />

        <main class="main-content">
            <div class="list-container">
                <div class="list-header">
                    <h2>Subject List</h2>
                    <a href="#" class="btn-add">+ Add New Subject</a>
                </div>

                <div class="search-container">
                    <div class="search-box">
                        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" class="search-input" placeholder="Search subjects by code, name, lecturer, or course...">
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Credits</th>
                            <th>Course</th>
                            <th>Lecturer</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                        <tr>
                            <td><strong>{{ $subject['code'] }}</strong></td>
                            <td>{{ $subject['name'] }}</td>
                            <td>{{ $subject['credits'] }}</td>
                            <td>{{ $subject['course'] }}</td>
                            <td>{{ $subject['lecturer'] }}</td>
                            <td>
                                <span class="type-badge">{{ $subject['type'] }}</span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $subject['status'] }}">
                                    {{ ucfirst($subject['status']) }}
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
                            <td colspan="8" style="text-align: center; padding: 2rem; color: #6b7280;">
                                No subjects found.
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

