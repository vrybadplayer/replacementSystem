@php
// FIX: Import Carbon here so it is available for timeslot formatting
use Carbon\Carbon;

// SCP-73 Security tokens (passed from controller)
$actionToken = $actionToken ?? null;
$confirmationId = $confirmationId ?? null;
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
            <div class="alert alert-success" style="padding: 1rem; background-color: #d1e7dd; border: 1px solid #a3cfb4; color: #0a3622; margin-bottom: 1rem; border-radius: 5px;">
                <strong>✓ Success:</strong> {{ session('success') }}
            </div>
            @endif
            
            @if(session('error'))
            <div class="alert alert-error" style="padding: 1rem; background-color: #f8d7da; border: 1px solid #f5c2c7; color: #58151c; margin-bottom: 1rem; border-radius: 5px;">
                <strong>✗ Error:</strong> {{ session('error') }}
            </div>
            @endif


            <!-- Start of Form -->
            <!-- The action points to the named route for form submission -->
            <form id="assignmentForm" action="{{ route('admin.assignment.submit') }}" method="POST">
                @csrf
                
                <!-- SCP-73: Security Tokens -->
                @if($actionToken && $confirmationId)
                <input type="hidden" name="action_token" value="{{ $actionToken }}">
                <input type="hidden" name="confirmation_id" value="{{ $confirmationId }}">
                @endif

                <!-- SCP-73: Security Confirmation Section -->
                @if($actionToken && $confirmationId)
                <div class="security-confirmation" style="margin: 0 0 20px 0; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <h4 style="margin-top: 0; color: #856404; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 20px;">⚠️</span> Security Confirmation Required
                    </h4>
                    <div style="margin-top: 10px;">
                        <label style="display: flex; align-items: flex-start; gap: 10px;">
                            <input type="checkbox" name="user_confirmation" value="yes" required 
                                   style="width: 18px; height: 18px; margin-top: 3px;"
                                   onchange="toggleSubmitButton()">
                            <div>
                                <strong style="color: #856404;">I confirm that I intend to create this class assignment.</strong>
                                <div style="margin-top: 5px; font-size: 0.85em; color: #856404;">
                                    <div>Confirmation ID: <code style="background: #fff; padding: 2px 6px; border-radius: 3px;">{{ $confirmationId }}</code></div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                @endif

                <!-- LAYER 1: Dropdowns -->
                <div class="filters-container">
                    <!-- 1. Course -->
                    <select name="courseId" id="courseDropdown" class="custom-dropdown" required>
                        <option value="" disabled selected>1. Select Course ▼</option>
                        @foreach($courses as $courseId => $courseCode)
                        <option value="{{ $courseId }}" {{ old('courseId') == $courseId ? 'selected' : '' }}>
                            {{ $courseCode }} ({{ $courseId }})
                        </option>
                        @endforeach
                    </select>

                    <!-- 2. Subject (ALL Subjects) -->
                    <select name="subjectId" id="subjectDropdown" class="custom-dropdown" required>
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
                        <input type="radio" name="day" value="{{ $dayValue }}" {{ old('day') == $dayValue ? 'checked' : '' }} required>
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
                        <input type="radio" name="venueId" value="{{ $venueId }}" {{ old('venueId') == $venueId ? 'checked' : '' }} required>
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
                        <input type="radio" name="selectedTimeslot" value="{{ $timeslotValue }}" {{ old('selectedTimeslot') == $timeslotValue ? 'checked' : '' }} required>
                        <span class="pill-btn">{{ $start }} - {{ $end }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="divider"></div>

                <!-- Footer -->
                <div class="footer-action">
                    <button id="submit-btn" type="submit" class="submit-btn" disabled>Create Assignment</button>
                </div>
            </form>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    
    <script>
        function toggleSubmitButton() {
            const confirmationCheckbox = document.querySelector('input[name="user_confirmation"]');
            const submitBtn = document.getElementById('submit-btn');
            
            if (confirmationCheckbox && submitBtn) {
                if (confirmationCheckbox.checked) {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = "1";
                    submitBtn.style.cursor = "pointer";
                    submitBtn.title = "";
                } else {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = "0.6";
                    submitBtn.style.cursor = "not-allowed";
                    submitBtn.title = "Please confirm the action first";
                }
            }
        }
        
        // Add visual feedback for selected radio buttons
        function setupRadioSelection() {
            document.querySelectorAll('.pill-radio input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove 'selected' class from all pills in this group
                    const groupName = this.name;
                    document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
                        r.parentElement.classList.remove('selected');
                    });
                    
                    // Add 'selected' class to the clicked one
                    this.parentElement.classList.add('selected');
                });
                
                // Initialize selection state
                if (radio.checked) {
                    radio.parentElement.classList.add('selected');
                }
            });
        }
        
        // Add visual feedback for selected checkboxes
        function setupCheckboxSelection() {
            document.querySelectorAll('.pill-checkbox input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        this.parentElement.classList.add('selected');
                    } else {
                        this.parentElement.classList.remove('selected');
                    }
                });
                
                // Initialize selection state
                if (checkbox.checked) {
                    checkbox.parentElement.classList.add('selected');
                }
            });
        }
        
        // Initialize everything on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleSubmitButton();
            setupRadioSelection();
            setupCheckboxSelection();
            
            // Add CSS for visual feedback
            const style = document.createElement('style');
            style.textContent = `
                .pill-radio.selected .pill-btn,
                .pill-checkbox.selected span {
                    background-color: #007bff !important;
                    color: white !important;
                    border-color: #007bff !important;
                }
                
                #submit-btn:disabled {
                    background-color: #6c757d !important;
                    border-color: #6c757d !important;
                    color: white !important;
                    opacity: 0.6;
                    cursor: not-allowed !important;
                }
                
                .security-confirmation code {
                    font-family: 'Courier New', monospace;
                    font-size: 0.9em;
                    letter-spacing: 0.5px;
                }
                
                .alert-success {
                    padding: 1rem;
                    background-color: #d1e7dd;
                    border: 1px solid #a3cfb4;
                    color: #0a3622;
                    margin-bottom: 1rem;
                    border-radius: 5px;
                }
                
                .alert-error {
                    padding: 1rem;
                    background-color: #f8d7da;
                    border: 1px solid #f5c2c7;
                    color: #58151c;
                    margin-bottom: 1rem;
                    border-radius: 5px;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>