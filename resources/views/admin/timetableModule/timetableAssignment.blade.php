<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Timetable Assignment</title>
    
    <!-- Load Global & Sidebar CSS -->
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    
    <!-- Load Page Specific CSS -->
    <link rel="stylesheet" href="{{ asset('css/pages/admin_assignment.css') }}">
</head>
<body>

    <!-- Header -->
    <header class="app-header">
        <h1>Admin Portal</h1>
    </header>

    <div class="app-container">
        
        <!-- Admin Sidebar Component -->
        <x-admin_sidebar />

        <main class="main-content">
            
            <h2 class="page-title">Timetable Assignment</h2>

            <!-- LAYER 1: Dropdowns -->
            <div class="filters-container">
                <select class="custom-dropdown" onchange="handleSubjectChange(this)">
                    <option value="" disabled selected>Subject Dropdown ▼</option>
                    <option value="BMIT3173">BMIT3173 - Integrative Prog</option>
                    <option value="BJEL2013">BJEL2013 - English</option>
                </select>

                <select class="custom-dropdown">
                    <option value="" disabled selected>Venue Type ▼</option>
                    <option value="lecture">Lecture Hall</option>
                    <option value="lab">Lab</option>
                </select>

                <select class="custom-dropdown">
                    <option value="" disabled selected>Class Groups ▼</option>
                    <option value="g1">G1</option>
                    <option value="g2">G2</option>
                </select>
            </div>

            <!-- LAYER 2: Day of Week -->
            <div class="section-label">Day of Week</div>
            <div class="options-container" id="day-container">
                <!-- Initially Disabled -->
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Mon')">Mon</button>
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Tue')">Tue</button>
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Wed')">Wed</button>
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Thu')">Thu</button>
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Fri')">Fri</button>
                <button class="pill-btn disabled" onclick="handleSelection('day', this, 'Sat')">Sat</button>
            </div>

            <div class="divider"></div>

            <!-- LAYER 3: Venue -->
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

            <!-- LAYER 4: Timeslot -->
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

            <!-- Footer -->
            <div class="footer-action">
                <button id="submit-btn" class="submit-btn" disabled>Submit</button>
            </div>

        </main>
    </div>

    <!-- Load Logic Script -->
    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script src="{{ asset('js/admin_assignment.js') }}"></script>
</body>
</html>