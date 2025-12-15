@php
// FIX: Import Carbon here so it is available for timeslot formatting
use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Timetable Assignment</title>

    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/admin_assignment.css') }}">
</head>

<body>

    <header class="app-header">
        <h1>Admin Portal</h1>
    </header>

    <div class="app-container">

        <!-- Admin Sidebar Component -->
        <x-admin_sidebar />

        <main class="main-content">

            <h2 class="page-title">Timetable Assignment</h2>

            <!-- Success/Error Message Display -->
            @if(session('success'))
            <div style="padding: 1rem; background-color: #d1e7dd; border: 1px solid #a3cfb4; color: #0a3622; margin-bottom: 1rem; border-radius: 5px;">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div style="padding: 1rem; background-color: #f8d7da; border: 1px solid #f5c2c7; color: #58151c; margin-bottom: 1rem; border-radius: 5px;">
                {{ session('error') }}
            </div>
            @endif


            <!-- Start of Form -->
            <!-- The action points to the named route for form submission -->
            <form id="assignmentForm" action="{{ route('admin.assignment.submit') }}" method="POST">
                @csrf

                <!-- LAYER 1: Dropdowns -->
                <div class="filters-container">
                    <!-- 1. Course -->
                    <select name="courseId" id="courseDropdown" class="custom-dropdown">
                        <option value="" disabled selected>1. Select Course ▼</option>
                        @foreach($courses as $courseId => $courseCode)
                        <option value="{{ $courseId }}" {{ old('courseId') == $courseId ? 'selected' : '' }}>
                            {{ $courseCode }} ({{ $courseId }})
                        </option>
                        @endforeach
                    </select>

                    <!-- 2. Subject (ALL Subjects) -->
                    <select name="subjectId" id="subjectDropdown" class="custom-dropdown">
                        <option value="" disabled selected>2. Select Subject ▼</option>
                        @foreach($allSubjects as $subject)
                        <option value="{{ $subject['subjectId'] }}" {{ old('subjectId') == $subject['subjectId'] ? 'selected' : '' }}>{{ $subject['subjectName'] }}</option>
                        @endforeach
                    </select>

                    <!-- Hidden Class Type Input (LOCKED TO LECTURE) -->
                    <input type="hidden" name="classType" value="{{ $classType }}">

                    <!-- Placeholder to display fixed type -->
                    <div class="custom-dropdown" style="background-color: #f3f4f6; color: #6b7280; cursor:default;">Type: {{ $classType }}</div>
                </div>

                <!-- 4. Class Groups (Checkboxes) -->
                <div class="section-label" style="margin-top: 1rem;">4. Class Groups (Select 1 or More)</div>
                <div class="options-container multi-select-group" id="classGroupContainer">
                    @foreach($classGroups as $group)
                    @php
                    $groupId = $group['classGroupId'];
                    $isChecked = in_array($groupId, old('classGroupIds', [])) ? 'checked' : '';
                    @endphp

                    <!-- FIX: Use <label class="pill-checkbox"> structure -->
                    <label class="pill-checkbox">
                        <!-- The checkbox input is hidden -->
                        <input type="checkbox" name="classGroupIds[]" value="{{ $groupId }}" {{ $isChecked }}>

                        <!-- The span is styled to look like the pill-btn -->
                        <span>{{ $groupId }}</span>
                    </label>
                    @endforeach
                </div>
                <div class="divider"></div>

                <!-- LAYER 2: Day of Week (Radio Buttons) -->
                <div class="section-label">Day of Week</div>
                <div class="options-container" id="day-container">
                    @foreach($daysOfWeek as $day)
                    @php $dayValue = $day; @endphp
                    <label class="pill-radio">
                        <input type="radio" name="day" value="{{ $dayValue }}" {{ old('day') == $dayValue ? 'checked' : '' }}>
                        <span class="pill-btn">{{ $day }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="divider"></div>

                <!-- LAYER 3: Venue (Radio Buttons) -->
                <div class="section-label">Venue</div>
                <div class="options-container" id="venue-container">
                    @foreach($venues as $venue)
                    @php $venueId = $venue['venueId']; @endphp
                    <label class="pill-radio">
                        <input type="radio" name="venueId" value="{{ $venueId }}" {{ old('venueId') == $venueId ? 'checked' : '' }}>
                        <span class="pill-btn">{{ $venueId }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="divider"></div>

                <!-- LAYER 4: Timeslot (Radio Buttons - HARDCODED 2-Hour LECTURE SLOTS) -->
                <div class="section-label">Timeslot (Duration: 2 Hours)</div>
                <div class="options-container" id="timeslot-container">
                    @foreach($timeSlots as $start)
                    @php
                    $end = Carbon::createFromFormat('H:i', $start)->addHours(2)->format('H:i');
                    $timeslotValue = $start;
                    @endphp
                    <label class="pill-radio">
                        <input type="radio" name="selectedTimeslot" value="{{ $timeslotValue }}" {{ old('selectedTimeslot') == $timeslotValue ? 'checked' : '' }}>
                        <span class="pill-btn">{{ $start }} - {{ $end }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="divider"></div>

                <!-- Footer -->
                <div class="footer-action">
                    <!-- Submit button is always enabled and backend handles required field checks -->
                    <button id="submit-btn" class="submit-btn">Submit</button>
                </div>
            </form>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>

</html>