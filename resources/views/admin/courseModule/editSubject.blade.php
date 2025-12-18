<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject - Admin</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/add_course.css') }}">
</head>
<body>
    <x-header title="Edit Subject" />
    
    <div class="app-container">
        <x-admin_sidebar />
        
        <main class="main-content">
            <div class="form-container">
                <div class="form-header">
                    <h2>Edit Subject</h2>
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

                <form method="POST" action="{{ route('admin.subjects.update', $subject['subjectId']) }}" class="user-form">
                    @csrf

                    <div class="form-group">
                        <label for="subjectId">Subject ID:</label>
                        <input type="text" id="subjectId" value="{{ $subject['subjectId'] ?? old('subjectId') }}" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                        <small style="color: #6b7280; font-size: 0.875rem;">Subject ID cannot be changed</small>
                    </div>

                    <div class="form-group">
                        <label for="subjectCode">Subject Code:</label>
                        <input type="text" id="subjectCode" name="subjectCode" value="{{ $subject['subjectCode'] ?? old('subjectCode') }}">
                        @error('subjectCode')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="subjectName">Subject Name:</label>
                        <input type="text" id="subjectName" name="subjectName" value="{{ $subject['subjectName'] ?? old('subjectName') }}">
                        @error('subjectName')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="creditHours">Credit Hours:</label>
                        <input type="number" id="creditHours" name="creditHours" value="{{ $subject['creditHours'] ?? old('creditHours') }}" min="0">
                        @error('creditHours')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select id="category" name="category">
                            <option value="">Select Category</option>
                            <option value="core" {{ ($subject['category'] ?? old('category')) === 'core' ? 'selected' : '' }}>Core</option>
                            <option value="general" {{ ($subject['category'] ?? old('category')) === 'general' ? 'selected' : '' }}>General</option>
                            <option value="elective" {{ ($subject['category'] ?? old('category')) === 'elective' ? 'selected' : '' }}>Elective</option>
                        </select>
                        @error('category')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status" required>
                            <option value="active" {{ ($subject['status'] ?? old('status')) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($subject['status'] ?? old('status')) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Update Subject</button>
                        <a href="{{ route('admin.subjects') }}" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

