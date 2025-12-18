<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course Subject - Admin</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/add_course.css') }}">
</head>
<body>
    <x-header title="Add Course Subject" />
    
    <div class="app-container">
        <x-admin_sidebar />
        
        <main class="main-content">
            <div class="form-container">
                <div class="form-header">
                    <h2>Add New Course Subject</h2>
                </div>

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.courseSubjects.store') }}" class="user-form">
                    @csrf

                    <div class="form-group">
                        <label for="courseId">Course:</label>
                        <select id="courseId" name="courseId">
                            <option value="">Select a Course</option>
                            @foreach($courses as $course)
                            <option value="{{ $course['courseId'] }}" {{ old('courseId') === $course['courseId'] ? 'selected' : '' }}>
                                {{ $course['courseId'] }} - {{ $course['courseCode'] }} ({{ $course['courseName'] }})
                            </option>
                            @endforeach
                        </select>
                        @error('courseId')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="subjectId">Subject:</label>
                        <select id="subjectId" name="subjectId">
                            <option value="">Select a Subject</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject['subjectId'] }}" {{ old('subjectId') === $subject['subjectId'] ? 'selected' : '' }}>
                                {{ $subject['subjectId'] }} - {{ $subject['subjectCode'] }} ({{ $subject['subjectName'] }})
                            </option>
                            @endforeach
                        </select>
                        @error('subjectId')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Create Course Subject</button>
                        <a href="{{ route('admin.courseSubjects') }}" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

