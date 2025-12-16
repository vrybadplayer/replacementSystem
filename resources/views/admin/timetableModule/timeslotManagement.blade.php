@php
use Carbon\Carbon;
// Set default values for robust template rendering
$courses = $courses ?? [];
$groups = $groups ?? [];
$filteredSlots = $filteredSlots ?? null;
$selectedCourse = $selectedCourse ?? null;
$selectedGroup = $selectedGroup ?? null;

$daysOfWeek = $daysOfWeek ?? [];
$venues = $venues ?? [];
$timeSlots = $timeSlots ?? [];
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Timeslot Management</title>

    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/admin_timeslot.css') }}">
</head>

<body>

    <header class="app-header">
        <h1>Admin Portal</h1>
    </header>

    <div class="app-container">

        <x-admin_sidebar />

        <main class="main-content">

            <h2 class="page-title">Timeslot Management</h2>

            @if(session('success'))
            <!-- ... success message ... -->
            @endif
            @if(session('error'))
            <!-- ... error message ... -->
            @endif

            <!-- 1. FILTER FORM (Always visible) -->
            <form id="filterForm" action="{{ route('admin.timeslot.filter') }}" method="POST">
                @csrf
                <div class="filters-row">
                    <!-- Course Dropdown -->
                    <select name="courseId" class="custom-dropdown" onchange="document.getElementById('filterForm').submit()">
                        <option value="" disabled selected>Course ▼</option>
                        @foreach($courses as $course)
                        <option value="{{ $course['courseId'] }}"
                            {{ ($selectedCourse == $course['courseId'] || old('courseId') == $course['courseId']) ? 'selected' : '' }}>
                            {{ $course['courseCode'] }}
                        </option>
                        @endforeach
                    </select>

                    <!-- Class Group Dropdown -->
                    <select name="classGroupId" class="custom-dropdown" id="group-dropdown" onchange="document.getElementById('filterForm').submit()">
                        <option value="" disabled selected>Class Groups ▼</option>
                        @foreach($groups as $group)
                        <option value="{{ $group['classGroupId'] }}"
                            {{ ($selectedGroup == $group['classGroupId'] || old('classGroupId') == $group['classGroupId']) ? 'selected' : '' }}>
                            {{ $group['classGroupId'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="divider"></div>

            <!-- 2. RESULT/MODIFY SECTION (Only display if filters have been applied and data is available) -->
            @if($filteredSlots)
            <form id="modifyForm" action="{{ route('admin.timeslot.update') }}" method="POST">
                @csrf

                <!-- Hidden inputs for UPDATE -->
                <input type="hidden" name="originalTimetableId" id="originalTimetableId" value="{{ old('originalTimetableId') }}">
                <input type="hidden" name="newDay" id="newDayInput" value="{{ old('newDay') }}">
                <input type="hidden" name="newVenueId" id="newVenueIdInput" value="{{ old('newVenueId') }}">
                <input type="hidden" name="newStartTime" id="newStartTimeInput" value="{{ old('newStartTime') }}">

                <div class="modify-section">
                    <label class="modify-label">Modify From:</label>

                    <!-- Subject Timeslot Dropdown (Results of the filter) -->
                    <select name="selectedTimetableId" id="subjectDropdown" class="custom-dropdown" style="min-width: 300px;" onchange="updateModifyFrom(this.value)">
                        <option value="" disabled selected>Subject Timeslot ▼</option>
                        @foreach($filteredSlots as $slot)
                        <option value="{{ $slot['timetableId'] }}">
                            {{ $slot['subjectCode'] }} ({{ $slot['classType'] }}) - {{ $slot['dayOfWeek'] }} {{ $slot['startTime'] }}
                        </option>
                        @endforeach
                    </select>
                    <!-- Delete Button (Removed per instruction, but placeholder left for completeness) -->
                    <!-- <button type="button" id="deleteBtn" class="save-btn" disabled style="background-color: #f8d7da;">Delete</button> -->
                </div>

                <div class="section-label">To:</div>

                <!-- 3. Day of Week -->
                <div class="section-label" style="margin-top: 1rem;">Day of Week</div>
                <div class="options-container" id="day-container">
                    @foreach($daysOfWeek as $day)
                    @php $dayLower = $day; @endphp
                    <label class="pill-radio">
                        <input type="radio" name="newDay" value="{{ $dayLower }}" onclick="updatePillSelection('newDay', this.value)" {{ old('newDay') == $dayLower ? 'checked' : '' }}>
                        <span class="pill-btn">{{ substr($day, 0, 3) }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="divider"></div>

                <!-- 4. Venue -->
                <div class="section-label">Venue</div>
                <div class="options-container" id="venue-container">
                    @foreach($venues as $venue)
                    <label class="pill-radio">
                        <input type="radio" name="newVenueId" value="{{ $venue['venueId'] }}" onclick="updatePillSelection('newVenueId', this.value)">
                        <span class="pill-btn">{{ $venue['venueId'] }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="divider"></div>

                <!-- 5. Timeslot -->
                <div class="section-label">Timeslot (2-Hour Intervals)</div>
                <div class="options-container" id="timeslot-container">
                    @foreach($timeSlots as $start)
                    @php $end = Carbon::createFromFormat('H:i', $start)->addHours(2)->format('H:i'); @endphp
                    <label class="pill-radio">
                        <input type="radio" name="newStartTime" value="{{ $start }}" onclick="updatePillSelection('newStartTime', this.value)">
                        <span class="pill-btn">{{ $start }} - {{ $end }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="divider"></div>

                <!-- Save Button -->
                <div class="footer-action">
                    <!-- We use JS to enable this button when all fields are selected -->
                    <button id="saveBtn" type="submit" class="save-btn" disabled>Save</button>
                </div>
            </form>
            @else
            <p class="text-gray-500" style="margin-top: 3rem;">Please select a Course and Class Group above and click 'Filter' (by selecting the Group).</p>
            @endif

        </main>
    </div>

    <!-- Minimal JS to handle pill selection and form validation (NO AJAX) -->
    <script>
        // Store the original timetable ID for the update action
        let originalTimetableId = document.getElementById('originalTimetableId')?.value || '';

        function updatePillSelection(type, value) {
            // Logic to update the hidden input fields and trigger validity check
            const inputId = `new${type.charAt(0).toUpperCase() + type.slice(1)}Input`;
            const input = document.getElementById(inputId);

            if (input) {
                input.value = value;
            }
            checkValidityAndEnableSave();
        }

        function updateModifyFrom(timetableId) {
            // When the "Modify From" dropdown changes, update the hidden ID
            originalTimetableId = timetableId;
            document.getElementById('originalTimetableId').value = timetableId;
            checkValidityAndEnableSave();
        }

        function checkValidityAndEnableSave() {
            // Check all inputs for required values
            const originalSelected = document.getElementById('originalTimetableId').value !== '';
            const daySelected = document.querySelector('input[name="newDay"]:checked') !== null;
            const venueSelected = document.querySelector('input[name="newVenueId"]:checked') !== null;
            const timeSelected = document.querySelector('input[name="newStartTime"]:checked') !== null;

            const saveBtn = document.getElementById('saveBtn');

            if (originalSelected && daySelected && venueSelected && timeSelected) {
                saveBtn.disabled = false;
            } else {
                saveBtn.disabled = true;
            }
        }

        // Add event listeners to the pills for visual feedback (since we don't have JS highlight)
        document.addEventListener('DOMContentLoaded', function() {
            // Check if the page has old input errors and needs to display the Save button state
            checkValidityAndEnableSave();
        });
    </script>
</body>

</html>