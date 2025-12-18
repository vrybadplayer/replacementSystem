<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Replacement Appeals</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/my_appeals.css') }}">
</head>
<body>
    <x-header title="Student Portal" />

    <div class="app-container">
        <x-student_sidebar />

        <main class="main-content">
            <div class="appeals-container">
                <div class="page-header">
                    <h2>My Replacement Appeals</h2>
                    <p style="color: #7f8c8d;">Track the status of your replacement class requests.</p>
                </div>

                @if(session('success'))
                    <div class="success-message">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                @if($appeals->count() > 0)
                    <div class="appeals-list">
                        @foreach($appeals as $appeal)
                            <div class="appeal-card">
                                <div class="appeal-header">
                                    <div class="appeal-id">Appeal #{{ $appeal->appealId }}</div>
                                    <span class="status-badge status-{{ strtolower($appeal->status) }}">
                                        {{ $appeal->status }}
                                    </span>
                                </div>

                                <div class="appeal-body">
                                    <div class="class-section" style="background-color: #ffebee; border: 2px solid #ef5350; border-radius: 8px; padding: 15px;">
                                        <h4 style="color: #c62828;">📅 Original Class (Missed)</h4>
                                        <div class="class-info">
                                            Subject: <strong>{{ $appeal->originalClass->subject->subjectId ?? 'N/A' }} - {{ $appeal->originalClass->subject->subjectName ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="class-info">
                                            Venue: {{ $appeal->originalClass->venue->venueId ?? 'N/A' }}
                                        </div>
                                        <div class="class-info">
                                            Type: {{ $appeal->originalClass->classType ?? 'N/A' }}
                                        </div>
                                        <div class="class-info">
                                            Date: {{ $appeal->originalClass->date ? date('D, d M Y', strtotime($appeal->originalClass->date)) : 'N/A' }}
                                        </div>
                                        <div class="class-info">
                                            Time: {{ $appeal->originalClass->startTime ?? 'N/A' }} - {{ $appeal->originalClass->endTime ?? 'N/A' }}
                                        </div>
                                        <div class="class-info">
                                            Group: {{ $appeal->originalClass->classGroupId ?? 'N/A' }}
                                        </div>
                                    </div>

                                    <div class="class-section" style="background-color: #e8f5e9; border: 2px solid #66bb6a; border-radius: 8px; padding: 15px;">
                                        <h4 style="color: #2e7d32;">✅ Replacement Class (Requested)</h4>
                                        <div class="class-info">
                                            Subject: <strong>{{ $appeal->requestedClass->subject->subjectId ?? 'N/A' }} - {{ $appeal->requestedClass->subject->subjectName ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="class-info">
                                            Venue: {{ $appeal->requestedClass->venue->venueId ?? 'N/A' }}
                                        </div>
                                        <div class="class-info">
                                            Type: {{ $appeal->requestedClass->classType ?? 'N/A' }}
                                        </div>
                                        <div class="class-info">
                                            Date: {{ $appeal->requestedClass->date ? date('D, d M Y', strtotime($appeal->requestedClass->date)) : 'N/A' }}
                                        </div>
                                        <div class="class-info">
                                            Time: {{ $appeal->requestedClass->startTime ?? 'N/A' }} - {{ $appeal->requestedClass->endTime ?? 'N/A' }}
                                        </div>
                                        <div class="class-info">
                                            Group: {{ $appeal->requestedClass->classGroupId ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="reason-section">
                                    <strong>Reason:</strong>
                                    {{ $appeal->reason }}
                                </div>

                                @if($appeal->proof)
                                    <div class="reason-section">
                                        <strong>Proof/Evidence:</strong>
                                        <div style="margin-top: 10px;">
                                            <img src="{{ asset('storage/' . $appeal->proof) }}" 
                                                 alt="Appeal Proof" 
                                                 style="max-width: 100%; max-height: 400px; border-radius: 8px; border: 1px solid #ddd;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <div style="display: none; padding: 10px; background: #f8d7da; color: #721c24; border-radius: 5px;">
                                                Unable to load image. Path: {{ $appeal->proof }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($appeal->status === 'Approved' || $appeal->status === 'Rejected')
                                    <div class="decision-section {{ $appeal->status === 'Rejected' ? 'rejected' : '' }}">
                                        <strong>Admin Decision:</strong>
                                        <div style="margin-top: 5px;">
                                            <div style="font-size: 13px; margin-bottom: 5px;">
                                                Reviewed by: <strong>{{ $appeal->adminUser->name ?? 'N/A' }}</strong>
                                            </div>
                                            <div style="font-size: 13px; margin-bottom: 5px;">
                                                Decision Date: {{ date('d M Y, h:i A', strtotime($appeal->decisionDate)) }}
                                            </div>
                                            @if($appeal->adminRemarks)
                                                <div style="font-size: 13px; margin-top: 10px; font-style: italic;">
                                                    "{{ $appeal->adminRemarks }}"
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <div class="appeal-footer">
                                    <div>
                                        Submitted: {{ date('d M Y, h:i A', strtotime($appeal->submissionDate)) }}
                                    </div>
                                    @if($appeal->status === 'Pending')
                                        <div style="color: #f39c12; font-weight: 600;">
                                            ⏳ Awaiting admin review...
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="no-appeals">
                        <h3>No appeals found</h3>
                        <p style="color: #95a5a6;">You haven't submitted any replacement appeals yet.</p>
                        <a href="{{ route('student.timetable') }}" class="btn-new-appeal">View My Timetable</a>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>
