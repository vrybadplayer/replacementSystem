<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - Admin</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/add_course.css') }}">
</head>
<body>
    <x-header title="Edit Course" />
    
    <div class="app-container">
        <x-admin_sidebar />
        
        <main class="main-content">
            <div class="form-container">
                <div class="form-header">
                    <h2>Edit Course</h2>
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

                <form method="POST" action="{{ route('admin.courses.update', $course['courseId']) }}" class="user-form">
                    @csrf

                    <div class="form-group">
                        <label for="courseId">Course ID:</label>
                        <input type="text" id="courseId" value="{{ $course['courseId'] ?? old('courseId') }}" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                        <small style="color: #6b7280; font-size: 0.875rem;">Course ID cannot be changed</small>
                    </div>

                    <div class="form-group">
                        <label for="courseCode">Course Code:</label>
                        <input type="text" id="courseCode" name="courseCode" value="{{ $course['courseCode'] ?? old('courseCode') }}" required>
                        @error('courseCode')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="courseName">Course Name:</label>
                        <input type="text" id="courseName" name="courseName" value="{{ $course['courseName'] ?? old('courseName') }}" required>
                        @error('courseName')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="faculty">Faculty:</label>
                        <input type="text" id="faculty" name="faculty" value="{{ $course['faculty'] ?? old('faculty') }}">
                        @error('faculty')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status" required>
                            <option value="active" {{ ($course['status'] ?? old('status')) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($course['status'] ?? old('status')) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Update Course</button>
                        <a href="{{ route('admin.courses') }}" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

