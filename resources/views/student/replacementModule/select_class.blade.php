<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Class for Appeal</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <style>
        .select-class-container {
            padding: 30px;
        }

        .page-header h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-info {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            color: #1976D2;
        }

        .alert-error {
            background: #ffebee;
            border-left: 4px solid #f44336;
            color: #c62828;
        }

        .classes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .class-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
        }

        .class-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .class-header {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .class-info {
            margin: 8px 0;
            color: #555;
            font-size: 14px;
        }

        .class-info strong {
            color: #2c3e50;
        }

        .btn-select {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }

        .btn-select:hover {
            background: #2980b9;
        }

        .no-classes {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            border: 2px dashed #e0e0e0;
        }

        .no-classes h3 {
            color: #7f8c8d;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <x-header title="Student Portal" />

    <div class="app-container">
        <x-student_sidebar />

        <main class="main-content">
            <div class="select-class-container">
                <div class="page-header">
                    <h2>Submit Replacement Appeal</h2>
                    <p style="color: #7f8c8d;">Select a replacement class you want to join</p>
                </div>

                @if(session('error'))
                    <div class="alert alert-error">
                        ✗ {{ session('error') }}
                    </div>
                @endif

                <div class="alert alert-info">
                    <strong>ℹ️ Note:</strong> Select a class from the list below that you want to join as a replacement. You'll need to provide the original class you missed and a valid reason.
                </div>

                @if($availableClasses->count() > 0)
                    <div class="classes-grid">
                        @foreach($availableClasses as $class)
                            <div class="class-card">
                                <div class="class-header">
                                    {{ $class->subject->subjectId }} - {{ $class->subject->subjectName }}
                                </div>
                                <div class="class-info">
                                    <strong>Date:</strong> {{ date('D, d M Y', strtotime($class->classDate)) }}
                                </div>
                                <div class="class-info">
                                    <strong>Type:</strong> {{ $class->classType }}
                                </div>
                                <div class="class-info">
                                    <strong>Time:</strong> {{ $class->startTime }} - {{ $class->endTime }}
                                </div>
                                <div class="class-info">
                                    <strong>Venue:</strong> {{ $class->venue->venueId }}
                                </div>
                                <div class="class-info">
                                    <strong>Group:</strong> {{ $class->classGroupId }}
                                </div>
                                <a href="{{ route('replacement.appeal', ['class_id' => $class->timetableId]) }}" class="btn-select">
                                    Select This Class
                                </a>
                            </div>
                        @endforeach
                    @else
                        <div class="no-classes">
                            <h3>No Available Classes</h3>
                            <p style="color: #95a5a6;">There are no upcoming classes available for replacement appeals.</p>
                            <p style="margin-top: 20px;">
                                <a href="{{ route('replacement.myAppeals') }}" style="color: #3498db;">View My Appeals</a>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>
