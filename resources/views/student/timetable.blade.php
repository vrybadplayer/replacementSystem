<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Timetable</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Small helper to match the table borders exactly */
        td, th { border: 1px solid #e5e7eb; }
    </style>
</head>
<body class="bg-white">

    <!-- Reusable Header Component -->
    <x-header title="Student Portal" />

    <div class="flex">
        
        <!-- Reusable Sidebar Component -->
        <x-sidebar />

        <!-- Main Content Area -->
        <main class="flex-1 p-8">

            <div class="flex justify-between items-start mb-6">
                <!-- Title & Filter -->
                <div>
                    <h2 class="text-2xl font-bold mb-4">My Timetable</h2>
                    
                    <div class="inline-block bg-gray-200 px-4 py-2 rounded">
                        <label class="font-bold mr-2">Date Filter</label>
                        <!-- Week Filter Dropdown -->
                        <select class="bg-transparent font-bold outline-none cursor-pointer">
                            @foreach($weeks as $date => $label)
                                <option value="{{ $date }}" {{ str_contains($label, 'Current') ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Conditional Reminder Text -->
                @if($hasReplacement)
                    <div class="text-red-500 text-xl font-medium max-w-lg text-right">
                        Reminder:<br>
                        You have one replacement class on 2025-12-24 at 1:00pm.
                    </div>
                @endif
            </div>

            <!-- Timetable Grid -->
            <div class="overflow-x-auto">
                <table class="w-full text-center text-xs border-collapse">
                    
                    <!-- Time Header Row -->
                    <thead>
                        <tr class="bg-gray-100 text-gray-600">
                            <th class="p-2 w-24">Day/Time</th>
                            @foreach($timeSlots as $time)
                                <th class="p-2 min-w-[80px]">{{ $time }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <!-- Body -->
                    <tbody>
                        @foreach($days as $day)
                            <tr>
                                <!-- Day Column -->
                                <td class="bg-gray-200 p-2 font-bold text-gray-700 h-24">
                                    {{ $day['name'] }} <br>
                                    <span class="text-[10px] font-normal">{{ $day['date'] }}</span>
                                </td>

                                <!-- Time Slot Logic -->
                                @php $skipNext = 0; @endphp
                                
                                @foreach($timeSlots as $time)
                                    
                                    {{-- If we are currently "under" a colspan from a previous cell, skip rendering --}}
                                    @if($skipNext > 0)
                                        @php $skipNext--; @endphp
                                        @continue
                                    @endif

                                    {{-- Check if there is an event starting at this Day + Time --}}
                                    @if(isset($scheduleData[$day['name']][$time]))
                                        @php 
                                            $event = $scheduleData[$day['name']][$time]; 
                                            // Set skip counter (Duration - 1)
                                            $skipNext = $event['duration'] - 1; 

                                            // Determine Color based on Type
                                            $colorClass = match($event['type']) {
                                                'normal' => 'bg-green-700 hover:bg-green-600 cursor-pointer',
                                                'replacement' => 'bg-yellow-600 cursor-not-allowed',
                                                'clash' => 'bg-red-700 cursor-not-allowed',
                                                default => 'bg-gray-300'
                                            };
                                        @endphp

                                        <td colspan="{{ $event['duration'] }}" class="p-1 align-middle">
                                            
                                            <!-- Logic: Only 'normal' classes wrap in a link -->
                                            @if($event['type'] === 'normal')
                                            <a href="{{ route('replacement.appeal', ['class_id' => $event['id']]) }}" class="block w-full h-full p-2 {{ $colorClass }} text-white rounded shadow-sm transition">
                                            @else
                                            <div class="block w-full h-full p-2 {{ $colorClass }} text-white rounded shadow-sm">
                                            @endif

                                                <!-- Class Details -->
                                                <div class="font-bold">{{ $event['subject'] }}</div>
                                                <div class="text-[10px]">{{ $event['venue'] }}</div>
                                                <div class="text-[10px]">{{ $event['lecturer'] }}</div>
                                                <div class="text-[10px] mt-1">{{ $event['time_str'] }}</div>
                                                
                                                @if(isset($event['note']))
                                                    <div class="text-[10px] mt-1 text-red-100 font-bold">{{ $event['note'] }}</div>
                                                @endif

                                            @if($event['type'] === 'normal')
                                            </a>
                                            @else
                                            </div>
                                            @endif

                                        </td>

                                    @else
                                        <!-- Empty Cell -->
                                        <td class="p-2"></td>
                                    @endif

                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Legend -->
            <div class="mt-8 flex gap-6 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-green-700 rounded"></div>
                    <span>Normal Class</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-yellow-600 rounded"></div>
                    <span>Replacement Class</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-red-700 rounded"></div>
                    <span>Clashed Class / Class on Public Holiday</span>
                </div>
            </div>

        </main>
    </div>

</body>
</html>