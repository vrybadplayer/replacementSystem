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
        <x-sidebar />

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
                                    <div class="class-section">
                                        <h4>📅 Original Class (Missed)</h4>
                                        <div class="class-info">
                                            <strong>{{ $appeal->originalClass->subject->subjectId }} - {{ $appeal->originalClass->subject->subjectName }}</strong>
                                        </div>
                                        <div class="class-info">
                                            📍 {{ $appeal->originalClass->venue->venueId }}
                                        </div>
                                        <div class="class-info">
                                            📅 {{ date('D, d M Y', strtotime($appeal->originalClass->date)) }}
                                        </div>
                                        <div class="class-info">
                                            ⏰ {{ $appeal->originalClass->startTime }} - {{ $appeal->originalClass->endTime }}
                                        </div>
                                    </div>

                                    <div class="class-section">
                                        <h4>✅ Replacement Class (Requested)</h4>
                                        <div class="class-info">
                                            <strong>{{ $appeal->requestedClass->subject->subjectId }} - {{ $appeal->requestedClass->subject->subjectName }}</strong>
                                        </div>
                                        <div class="class-info">
                                            📍 {{ $appeal->requestedClass->venue->venueId }}
                                        </div>
                                        <div class="class-info">
                                            📅 {{ date('D, d M Y', strtotime($appeal->requestedClass->date)) }}
                                        </div>
                                        <div class="class-info">
                                            ⏰ {{ $appeal->requestedClass->startTime }} - {{ $appeal->requestedClass->endTime }}
                                        </div>
                                        <div class="class-info">
                                            👥 Group {{ $appeal->requestedClass->classGroup->classGroupId ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="reason-section">
                                    <strong>Reason:</strong>
                                    {{ $appeal->reason }}
                                </div>

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
