<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Subject List - Admin Dashboard</title>
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
                    <h2>Course Subject List</h2>
                    <a href="{{ route('admin.courseSubjects.create') }}" class="btn-add">+ Add New Course Subject</a>
                </div>

                @if(session('success'))
                <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
                    {{ session('success') }}
                </div>
                @endif

                @if(isset($error))
                <div style="background: #fee; color: #c33; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    {{ $error }}
                </div>
                @endif

                <div class="search-container">
                    <form method="GET" action="{{ route('admin.courseSubjects') }}" class="search-form">
                        <div class="search-box">
                            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input type="text" name="search" class="search-input" placeholder="Search by Course Subject ID, Course ID, Subject ID, Course Code, or Subject Code..." value="{{ $search ?? '' }}">
                        </div>
                    </form>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Course Subject ID</th>
                            <th>Course</th>
                            <th>Subject</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courseSubjects as $cs)
                        <tr>
                            <td><strong>{{ $cs['courseSubjectId'] ?? '' }}</strong></td>
                            <td>
                                <strong>{{ $cs['courseId'] ?? '' }}</strong> - 
                                {{ $cs['course']['courseCode'] ?? 'N/A' }}
                            </td>
                            <td>
                                <strong>{{ $cs['subjectId'] ?? '' }}</strong> - 
                                {{ $cs['subject']['subjectCode'] ?? 'N/A' }}
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.courseSubjects.edit', $cs['courseSubjectId']) }}" class="btn-edit">Edit</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: #6b7280;">
                                @if(isset($error))
                                    {{ $error }}
                                @else
                                    No course subjects found.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($courseSubjects->count() > 0)
                    <div class="pagination-container">
                        {{ $courseSubjects->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>

