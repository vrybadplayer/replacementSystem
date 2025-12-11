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
        
        <!-- Sidebar (You might want to duplicate the sidebar file to highlight 'Timeslot Management' active state) -->
        <x-admin_sidebar />

        <main class="main-content">
            
            <h2 class="page-title">Timeslot Management</h2>

            <!-- 1. Top Filters -->
            <div class="filters-row">
                <select class="custom-dropdown" onchange="handleCourseChange(this)">
                    <option value="" disabled selected>Course ▼</option>
                    <option value="RSD">RSD</option>
                    <option value="DFT">DFT</option>
                </select>

                <select id="group-dropdown" class="custom-dropdown" disabled onchange="handleGroupChange(this)">
                    <option value="" disabled selected>Class Groups ▼</option>
                    <option value="G1">G1</option>
                    <option value="G2">G2</option>
                    <option value="G4">G4</option>
                </select>
            </div>

            <!-- 2. Modify From Section -->
            <div class="modify-section">
                <label class="modify-label">Modify From:</label>
                <!-- This dropdown would be populated via AJAX based on Group -->
                <select id="subject-dropdown" class="custom-dropdown" style="min-width: 300px;" disabled onchange="handleSubjectChange(this)">
                    <option value="" disabled selected>Subject Timeslot ▼</option>
                    <option value="101">BMIT3173(L) - Mon 8-10 (DK4)</option>
                    <option value="102">BJEL2013(T) - Wed 10-12 (K303)</option>
                </select>
            </div>

            <div class="section-label">To:</div>

            <!-- 3. Day of Week -->
            <div class="section-label" style="margin-top: 1rem;">Day of Week</div>
            <div class="options-container" id="day-container">
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Mon')">Mon</button>
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Tue')">Tue</button>
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Wed')">Wed</button>
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Thu')">Thu</button>
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Fri')">Fri</button>
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Sat')">Sat</button>
            </div>

            <div class="divider"></div>

            <!-- 4. Venue -->
            <div class="section-label">Venue</div>
            <div class="options-container" id="venue-container">
                <button class="pill-btn disabled" onclick="handleSelection('venue', this, 'V001')">V001</button>
                <button class="pill-btn disabled" onclick="handleSelection('venue', this, 'V002')">V002</button>
                <button class="pill-btn disabled" onclick="handleSelection('venue', this, 'V003')">V003</button>
                <button class="pill-btn disabled" onclick="handleSelection('venue', this, 'V004')">V004</button>
                <button class="pill-btn disabled" onclick="handleSelection('venue', this, 'V005')">V005</button>
                <button class="pill-btn disabled" onclick="handleSelection('venue', this, 'V006')">V006</button>
            </div>

            <div class="divider"></div>

            <!-- 5. Timeslot -->
            <div class="section-label">Timeslot</div>
            <div class="options-container" id="timeslot-container">
                <button class="pill-btn disabled" onclick="handleSelection('timeslot', this, '12-2')">12-2</button>
                <button class="pill-btn disabled" onclick="handleSelection('timeslot', this, '2-4')">2-4</button>
                <button class="pill-btn disabled" onclick="handleSelection('timeslot', this, '4-6')">4-6</button>
                <button class="pill-btn disabled" onclick="handleSelection('timeslot', this, '6-8')">6-8</button>
                <button class="pill-btn disabled" onclick="handleSelection('timeslot', this, '8-10')">8-10</button>
                <button class="pill-btn disabled" onclick="handleSelection('timeslot', this, '10-12')">10-12</button>
            </div>

            <div class="divider"></div>

            <!-- Save Button -->
            <div class="footer-action">
                <button id="save-btn" class="save-btn" disabled>Save</button>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script src="{{ asset('js/admin_timeslot.js') }}"></script>
</body>
</html>