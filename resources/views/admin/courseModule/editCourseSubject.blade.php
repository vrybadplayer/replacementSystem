<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course Subject - Admin</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/add_course.css') }}">
</head>
<body>
    <x-header title="Edit Course Subject" />
    
    <div class="app-container">
        <x-admin_sidebar />
        
        <main class="main-content">
            <div class="form-container">
                <div class="form-header">
                    <h2>Edit Course Subject</h2>
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

                <form method="POST" action="{{ route('admin.courseSubjects.update', $courseSubject['courseSubjectId']) }}" class="user-form">
                    @csrf

                    <div class="form-group">
                        <label for="courseSubjectId">Course Subject ID:</label>
                        <input type="text" id="courseSubjectId" value="{{ $courseSubject['courseSubjectId'] ?? old('courseSubjectId') }}" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                        <small style="color: #6b7280; font-size: 0.875rem;">Course Subject ID cannot be changed</small>
                    </div>

                    <div class="form-group">
                        <label for="courseId">Course:</label>
                        <select id="courseId" name="courseId" required>
                            <option value="">Select a Course</option>
                            @foreach($courses as $course)
                            <option value="{{ $course['courseId'] }}" {{ ($courseSubject['courseId'] ?? old('courseId')) === $course['courseId'] ? 'selected' : '' }}>
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
                        <select id="subjectId" name="subjectId" required>
                            <option value="">Select a Subject</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject['subjectId'] }}" {{ ($courseSubject['subjectId'] ?? old('subjectId')) === $subject['subjectId'] ? 'selected' : '' }}>
                                {{ $subject['subjectId'] }} - {{ $subject['subjectCode'] }} ({{ $subject['subjectName'] }})
                            </option>
                            @endforeach
                        </select>
                        @error('subjectId')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Update Course Subject</button>
                        <a href="{{ route('admin.courseSubjects') }}" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

