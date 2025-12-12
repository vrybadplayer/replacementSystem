<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Timetable</title>
    
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/timetable.css') }}">
</head>
<body>

    <header class="app-header">
        <h1>Student Portal</h1>
    </header>

    <div class="app-container">
    
        <x-student_sidebar />

        <main class="main-content">
            
            <!-- Reminder Alert -->
            @if($hasReplacement)
            <div class="reminder-box">
                <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <strong>Replacement Class Reminder</strong><br>
                    <span>You have one replacement class on 2025-12-24 at 1:00pm.</span>
                </div>
            </div>
            @endif

            <!-- Page Title & Filter -->
            <div class="controls-section">
                <h2>My Timetable</h2>
                
                <form action="{{ route('student.timetable') }}" method="GET" class="filter-wrapper">
                    <label>Date Filter:</label>
                    <select name="date" onchange="this.form.submit()" class="filter-select">
                        @foreach($weeks as $date => $label)
                            <option value="{{ $date }}" {{ (request('date') == $date || $loop->index == 1) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </form>
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
                                            $skipNext = $event['duration'] - 1; 
                                            $typeClass = match($event['type']) {
                                                'normal' => 'slot-normal',
                                                'replacement' => 'slot-replace',
                                                'clash' => 'slot-clash',
                                                default => ''
                                            };
                                        @endphp

                                        <td colspan="{{ $event['duration'] }}">
                                            @if($event['type'] === 'normal')
                                            <a href="{{ route('replacement.appeal', ['class_id' => $event['id']]) }}" class="class-slot {{ $typeClass }}">
                                            @else
                                            <div class="class-slot {{ $typeClass }}">
                                            @endif
                                                <div class="slot-subject">{{ $event['subject'] }}</div>
                                                <div class="slot-info">{{ $event['venue'] }}</div>
                                                <div class="slot-info">{{ $event['time_str'] }}</div>
                                                @if(isset($event['note']))
                                                    <div class="slot-info slot-note">{{ $event['note'] }}</div>
                                                @endif
                                            @if($event['type'] === 'normal')
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

            <div class="legend">
                <div class="legend-item"><div class="legend-color bg-normal"></div><span>Normal Class</span></div>
                <div class="legend-item"><div class="legend-color bg-replace"></div><span>Replacement Class</span></div>
                <div class="legend-item"><div class="legend-color bg-clash"></div><span>Canceled Class</span></div>
            </div>

        </main>
    </div>

    <!-- Logic for Sidebar -->
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>