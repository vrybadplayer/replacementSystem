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

// SCP-73 Security tokens
$actionToken = $actionToken ?? null;
$confirmationId = $confirmationId ?? null;
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
            <div class="alert alert-success" style="padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 4px; margin-bottom: 20px;">
                <strong>✓ Success:</strong> {{ session('success') }}
            </div>
            @endif
            
            @if(session('error'))
            <div class="alert alert-error" style="padding: 15px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
                <strong>✗ Error:</strong> {{ session('error') }}
            </div>
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
                
                <!-- SCP-73: Security Tokens -->
                @if($actionToken && $confirmationId)
                <input type="hidden" name="action_token" value="{{ $actionToken }}">
                <input type="hidden" name="confirmation_id" value="{{ $confirmationId }}">
                @endif

                <!-- Hidden inputs for UPDATE -->
                <input type="hidden" name="originalTimetableId" id="originalTimetableId" value="{{ old('originalTimetableId') }}">
                <input type="hidden" name="newDay" id="newDayInput" value="{{ old('newDay') }}">
                <input type="hidden" name="newVenueId" id="newVenueIdInput" value="{{ old('newVenueId') }}">
                <input type="hidden" name="newStartTime" id="newStartTimeInput" value="{{ old('newStartTime') }}">

                <!-- SCP-73: Security Confirmation Section -->
                @if($actionToken && $confirmationId)
                <div class="security-confirmation" style="margin: 20px 0; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <h4 style="margin-top: 0; color: #856404; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 20px;">⚠️</span> Security Confirmation Required
                    </h4>
                    <div style="margin-top: 10px;">
                        <label style="display: flex; align-items: flex-start; gap: 10px;">
                            <input type="checkbox" name="user_confirmation" value="yes" required 
                                   style="width: 18px; height: 18px; margin-top: 3px;">
                            <div>
                                <strong style="color: #856404;">I confirm that I intend to modify this scheduled timeslot.</strong>
                                <div style="margin-top: 5px; font-size: 0.85em; color: #856404;">
                                    <div>Confirmation ID: <code style="background: #fff; padding: 2px 6px; border-radius: 3px;">{{ $confirmationId }}</code></div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                @endif

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
                        <input type="radio" name="newVenueId" value="{{ $venue['venueId'] }}" onclick="updatePillSelection('newVenueId', this.value)" {{ old('newVenueId') == $venue['venueId'] ? 'checked' : '' }}>
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
                        <input type="radio" name="newStartTime" value="{{ $start }}" onclick="updatePillSelection('newStartTime', this.value)" {{ old('newStartTime') == $start ? 'checked' : '' }}>
                        <span class="pill-btn">{{ $start }} - {{ $end }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="divider"></div>

                <!-- Save Button -->
                <div class="footer-action">
                    <button id="saveBtn" type="submit" class="save-btn" disabled>Save Changes</button>
                </div>
            </form>
            @else
            <div class="empty-state" style="margin-top: 3rem; text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px; border: 2px dashed #dee2e6;">
                <p style="color: #6c757d; font-size: 1.1em; margin-bottom: 10px;">
                    Please select a Course and Class Group above.
                </p>
                <p style="color: #adb5bd; font-size: 0.95em;">
                    The form will automatically submit when you make a selection.
                </p>
            </div>
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
            
            // Also check SCP-73 confirmation checkbox if it exists
            const confirmationCheckbox = document.querySelector('input[name="user_confirmation"]');
            const confirmationChecked = !confirmationCheckbox || confirmationCheckbox.checked;

            const saveBtn = document.getElementById('saveBtn');

            if (originalSelected && daySelected && venueSelected && timeSelected && confirmationChecked) {
                saveBtn.disabled = false;
                saveBtn.style.opacity = "1";
                saveBtn.style.cursor = "pointer";
            } else {
                saveBtn.disabled = true;
                saveBtn.style.opacity = "0.6";
                saveBtn.style.cursor = "not-allowed";
            }
        }

        // Add event listeners to the pills for visual feedback
        document.addEventListener('DOMContentLoaded', function() {
            // Check if the page has old input errors and needs to display the Save button state
            checkValidityAndEnableSave();
            
            // Add visual feedback for selected pills
            document.querySelectorAll('.pill-radio input').forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove 'selected' class from all pills in this group
                    const groupName = this.name;
                    document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
                        r.parentElement.classList.remove('selected');
                    });
                    
                    // Add 'selected' class to the clicked one
                    this.parentElement.classList.add('selected');
                });
            });
            
            // Check confirmation checkbox for SCP-73 compliance
            const confirmationCheckbox = document.querySelector('input[name="user_confirmation"]');
            if (confirmationCheckbox) {
                confirmationCheckbox.addEventListener('change', checkValidityAndEnableSave);
            }
        });
    </script>
    
    <style>
        .pill-radio.selected .pill-btn {
            background-color: #007bff !important;
            color: white !important;
            border-color: #007bff !important;
        }
        
        .security-confirmation code {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }
        
        .save-btn:disabled {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
        }
    </style>
</body>
</html>