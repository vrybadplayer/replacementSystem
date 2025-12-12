<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Replacement Options</title>
    
    <!-- 1. Load Global CSS -->
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    
    <!-- 2. Load Sidebar CSS -->
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    
    <!-- 3. Load Page-Specific CSS -->
    <link rel="stylesheet" href="{{ asset('css/pages/replacementOptions.css') }}">
</head>
<body>

    <header class="app-header">
        <h1>Student Portal</h1>
    </header>

    <div class="app-container">
        
        <!-- Reusable Sidebar -->
        <x-student_sidebar />

        <main class="main-content">
            
            <h2 class="page-title">Replacement Options</h2>

            <!-- User Info (Hardcoded from Controller) -->
            <div class="user-info-row">
                <div>
                    <span class="info-label">Course:</span> 
                    <span class="info-value">{{ $userInfo['course'] }}</span>
                </div>
                <div>
                    <span class="info-label">Class Group:</span> 
                    <span class="info-value">{{ $userInfo['group'] }}</span>
                </div>
            </div>

            <!-- Table Headers -->
            <div class="list-header">
                <div class="header-day">Day</div>
                <div class="header-details">
                    <span class="h-subject">Subject Code</span>
                    <span class="h-time">Timeslot</span>
                    <span class="h-lecturer">Lecturer</span>
                    <span class="h-venue">Venue</span>
                </div>
            </div>

            <!-- Loop through Days -->
            @foreach($groupedOptions as $day => $classes)
            <div class="day-group">
                
                <!-- Left Column: Day -->
                <div class="day-col">
                    {{ $day }}:
                </div>

                <!-- Right Column: List of Cards -->
                <div class="cards-col">
                    @foreach($classes as $class)
                        <!-- Clickable Card -> Redirects to Appeal Page -->
                        <a href="{{ route('replacement.appeal', ['class_id' => $class['id']]) }}" class="option-card">
                            <span class="col-subject">{{ $class['subject'] }}</span>
                            <span class="col-time">{{ $class['time'] }}</span>
                            <span class="col-lecturer">{{ $class['lecturer'] }}</span>
                            <span class="col-venue">{{ $class['venue'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endforeach

        </main>
    </div>

    <!-- Sidebar Logic -->
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>