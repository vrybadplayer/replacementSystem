<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Timeslot</title>
    
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <!-- Specific CSS for this page -->
    <link rel="stylesheet" href="{{ asset('css/pages/selectTimeslot.css') }}">
</head>
<body>

    <header class="app-header">
        <h1>Student Portal</h1>
    </header>

    <div class="app-container">
        
        <x-sidebar />

        <main class="main-content">

            <!-- Title Section -->
            <div class="selection-info">
                <h2 class="selection-title">Select Timeslot</h2>
                <div class="selection-details">
                    <span>Subject: <strong>{{ $selectedSubject }}</strong></span>
                    <span>Day of Week: <strong>{{ $selectedDay }}</strong></span>
                </div>
            </div>

            <!-- Timetable Grid -->
            <div class="timetable-wrapper">
                <table class="timetable">
                    <thead>
                        <tr>
                            <th>Day/Time</th>
                            @foreach($timeSlots as $time)
                                <th>{{ $time }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                            <tr>
                                <td class="day-column">
                                    {{ $day['name'] }}<br>
                                    <small>{{ $day['date'] }}</small>
                                </td>

                                @php $skipNext = 0; @endphp
                                @foreach($timeSlots as $time)
                                    @if($skipNext > 0)
                                        @php $skipNext--; @endphp
                                        @continue
                                    @endif

                                    @if(isset($scheduleData[$day['name']][$time]))
                                        @php 
                                            $event = $scheduleData[$day['name']][$time];
                                            // Handle cases where multiple classes might define the same slot in array_merge
                                            // For this mock, we assume simple structure.
                                            if(is_array($event) && isset($event[0])) $event = $event[0]; 
                                            
                                            $skipNext = $event['duration'] - 1; 

                                            // Determine Class based on 'type' set in Controller
                                            $cssClass = match($event['type']) {
                                                'existing' => 'slot-existing',
                                                'candidate' => 'slot-candidate',
                                                default => 'slot-existing'
                                            };
                                        @endphp

                                        <td colspan="{{ $event['duration'] }}">
                                            <!-- Logic: Only Candidates are links -->
                                            @if($event['type'] === 'candidate')
                                                <a href="{{ route('replacement.appeal', ['class_id' => $event['id']]) }}" class="class-slot {{ $cssClass }}">
                                            @else
                                                <div class="class-slot {{ $cssClass }}">
                                            @endif
                                                
                                                <div class="slot-subject">{{ $event['subject'] }}</div>
                                                <div class="slot-info">{{ $event['venue'] }}</div>
                                                
                                                @if(isset($event['lecturer']))
                                                <div class="slot-info">{{ $event['lecturer'] }}</div>
                                                @endif

                                            @if($event['type'] === 'candidate')
                                                </a>
                                            @else
                                                </div>
                                            @endif
                                        </td>
                                    @else
                                        <td></td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Simple Legend -->
            <div style="margin-top: 1.5rem; display: flex; gap: 1.5rem; font-size: 0.9rem;">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <div style="width:20px; height:20px; background-color:#15803d; border-radius:4px;"></div>
                    <span>Valid Option</span>
                </div>
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <div style="width:20px; height:20px; background-color:#9ca3af; border-radius:4px;"></div>
                    <span>Existing Class / Unavailable</span>
                </div>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>