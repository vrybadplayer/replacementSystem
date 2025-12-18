<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject - Admin</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/add_course.css') }}">
</head>
<body>
    <x-header title="Add Subject" />
    
    <div class="app-container">
        <x-admin_sidebar />
        
        <main class="main-content">
            <div class="form-container">
                <div class="form-header">
                    <h2>Add New Subject</h2>
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

                <form method="POST" action="{{ route('admin.subjects.store') }}" class="user-form">
                    @csrf

                    <div class="form-group">
                        <label for="subjectCode">Subject Code:</label>
                        <input type="text" id="subjectCode" name="subjectCode" value="{{ old('subjectCode') }}">
                        @error('subjectCode')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="subjectName">Subject Name:</label>
                        <input type="text" id="subjectName" name="subjectName" value="{{ old('subjectName') }}" required>
                        @error('subjectName')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="creditHours">Credit Hours:</label>
                        <input type="number" id="creditHours" name="creditHours" value="{{ old('creditHours') }}" min="0">
                        @error('creditHours')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select id="category" name="category">
                            <option value="">Select Category</option>
                            <option value="core" {{ old('category') === 'core' ? 'selected' : '' }}>Core</option>
                            <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>General</option>
                            <option value="elective" {{ old('category') === 'elective' ? 'selected' : '' }}>Elective</option>
                        </select>
                        @error('category')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status" required>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Create Subject</button>
                        <a href="{{ route('admin.subjects') }}" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

