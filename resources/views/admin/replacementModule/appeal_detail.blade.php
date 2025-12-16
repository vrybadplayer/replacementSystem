<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appeal Details - Admin</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/admin_appeal_detail.css') }}">
</head>
<body>
    <x-header title="Admin Dashboard" />

    <div class="app-container">
        <x-admin_sidebar />

        <main class="main-content">
            <div class="appeal-detail-container">
                <div class="page-header">
                    <div>
                        <a href="{{ route('admin.replacement.appeals') }}" class="back-link">← Back to Appeals List</a>
                        <div class="appeal-id-header" style="margin-top: 10px;">
                            <h2 style="margin: 0; color: #2c3e50;">Appeal #{{ $appeal->appealId }}</h2>
                            <span class="status-badge status-{{ strtolower($appeal->status) }}">
                                {{ $appeal->status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="content-grid">
                    <!-- Student Information -->
                    <div class="card">
                        <h3>👤 Student Information</h3>
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="info-label">Student Name</div>
                                <div class="info-value">{{ $appeal->user->name }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">User ID</div>
                                <div class="info-value">{{ $appeal->user->userId }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $appeal->user->email }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Role</div>
                                <div class="info-value">{{ $appeal->user->role }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Submission Date</div>
                                <div class="info-value">{{ date('d M Y, h:i A', strtotime($appeal->submissionDate)) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Class Comparison -->
                    <div class="card">
                        <h3>📚 Class Details</h3>
                        <div class="class-comparison">
                            <div class="class-box original">
                                <h4>📅 Original Class (Missed)</h4>
                                <div class="info-row" style="margin-bottom: 10px;">
                                    <div class="info-label">Subject</div>
                                    <div class="info-value">{{ $appeal->originalClass->subject->subjectId ?? 'N/A' }} - {{ $appeal->originalClass->subject->subjectName ?? 'N/A' }}</div>
                                </div>
                                <div class="info-row" style="margin-bottom: 10px;">
                                    <div class="info-label">Date & Time</div>
                                    <div class="info-value">{{ $appeal->originalClass->date ? date('d M Y', strtotime($appeal->originalClass->date)) : 'N/A' }}</div>
                                    <div class="info-value">{{ $appeal->originalClass->startTime }} - {{ $appeal->originalClass->endTime }}</div>
                                </div>
                                <div class="info-row" style="margin-bottom: 10px;">
                                    <div class="info-label">Venue</div>
                                    <div class="info-value">{{ $appeal->originalClass->venue->venueId ?? 'N/A' }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Group</div>
                                    <div class="info-value">{{ $appeal->originalClass->classGroup->classGroupId ?? 'N/A' }}</div>
                                </div>
                            </div>

                            <div class="class-box replacement">
                                <h4>✅ Replacement Class (Requested)</h4>
                                <div class="info-row" style="margin-bottom: 10px;">
                                    <div class="info-label">Subject</div>
                                    <div class="info-value">{{ $appeal->requestedClass->subject->subjectId ?? 'N/A' }} - {{ $appeal->requestedClass->subject->subjectName ?? 'N/A' }}</div>
                                </div>
                                <div class="info-row" style="margin-bottom: 10px;">
                                    <div class="info-label">Date & Time</div>
                                    <div class="info-value">{{ $appeal->requestedClass->date ? date('d M Y', strtotime($appeal->requestedClass->date)) : 'N/A' }}</div>
                                    <div class="info-value">{{ $appeal->requestedClass->startTime }} - {{ $appeal->requestedClass->endTime }}</div>
                                </div>
                                <div class="info-row" style="margin-bottom: 10px;">
                                    <div class="info-label">Venue</div>
                                    <div class="info-value">{{ $appeal->requestedClass->venue->venueId ?? 'N/A' }}</div>
                                </div>
                                <div class="info-row" style="margin-bottom: 10px;">
                                    <div class="info-label">Group</div>
                                    <div class="info-value">{{ $appeal->requestedClass->classGroup->classGroupId ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reason and Proof -->
                    <div class="card">
                        <h3>📝 Appeal Reason</h3>
                        <div style="padding: 15px; background: #fff9e6; border-left: 4px solid #ffc107; border-radius: 6px;">
                            {{ $appeal->reason }}
                        </div>
                        
                        @if($appeal->proof)
                            <div class="proof-section">
                                <strong>Supporting Document:</strong>
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
                        @else
                            <div class="proof-section">
                                <strong>Supporting Document:</strong>
                                <div style="margin-top: 10px; color: #7f8c8d;">
                                    No document uploaded
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Validation Checks -->
                    <div class="card">
                        <h3>✓ Validation Checks</h3>
                        <div class="validation-checks">
                            <div class="check-item {{ $validationChecks['same_week'] ? 'pass' : 'fail' }}">
                                <span class="check-icon">{{ $validationChecks['same_week'] ? '✓' : '✗' }}</span>
                                <span>Replacement class is in the same week</span>
                            </div>
                            <div class="check-item {{ $validationChecks['after_submission'] ? 'pass' : 'fail' }}">
                                <span class="check-icon">{{ $validationChecks['after_submission'] ? '✓' : '✗' }}</span>
                                <span>Replacement class is after submission date</span>
                            </div>
                            <div class="check-item {{ $validationChecks['no_clash'] ? 'pass' : 'fail' }}">
                                <span class="check-icon">{{ $validationChecks['no_clash'] ? '✓' : '✗' }}</span>
                                <span>No timetable clash detected</span>
                            </div>
                            <div class="check-item {{ $validationChecks['has_capacity'] ? 'pass' : 'fail' }}">
                                <span class="check-icon">{{ $validationChecks['has_capacity'] ? '✓' : '✗' }}</span>
                                <span>Class has available capacity (< 5 replacement students)</span>
                            </div>
                            <div class="check-item {{ $validationChecks['valid_account'] ? 'pass' : 'fail' }}">
                                <span class="check-icon">{{ $validationChecks['valid_account'] ? '✓' : '✗' }}</span>
                                <span>Student account is active</span>
                            </div>
                            <div class="check-item {{ $validationChecks['same_subject'] ? 'pass' : 'fail' }}">
                                <span class="check-icon">{{ $validationChecks['same_subject'] ? '✓' : '✗' }}</span>
                                <span>Same subject code</span>
                            </div>
                        </div>
                    </div>

                    <!-- Decision Section -->
                    @if($appeal->status === 'Pending')
                        <div class="card">
                            <h3>⚖️ Make Decision</h3>
                            <form action="{{ route('admin.replacement.process', $appeal->appealId) }}" method="POST" class="decision-form">
                                @csrf
                                <div class="form-group">
                                    <label for="admin_remarks">Admin Remarks (Optional)</label>
                                    <textarea id="admin_remarks" name="admin_remarks" placeholder="Enter any comments or reasons for your decision..."></textarea>
                                </div>
                                <div class="button-group">
                                    <button type="submit" name="decision" value="approve" class="btn btn-approve">
                                        ✓ Approve Appeal
                                    </button>
                                    <button type="submit" name="decision" value="reject" class="btn btn-reject">
                                        ✗ Reject Appeal
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="card">
                            <h3>⚖️ Decision</h3>
                            <div class="decided-info {{ $appeal->status === 'Rejected' ? 'rejected' : '' }}">
                                <div style="font-size: 16px; font-weight: 600; margin-bottom: 10px;">
                                    Status: {{ $appeal->status }}
                                </div>
                                @if($appeal->status === 'Approved' && $appeal->replacement)
                                    <div style="margin-bottom: 10px; padding: 10px; background: #e8f5e9; border-radius: 6px;">
                                        <strong>✅ Replacement ID:</strong> <span style="color: #2e7d32; font-weight: 600;">{{ $appeal->replacement->replacementId }}</span>
                                    </div>
                                @endif
                                <div style="margin-bottom: 5px;">
                                    <strong>Reviewed by:</strong> {{ $appeal->adminUser->name ?? 'N/A' }} ({{ $appeal->adminUser->userId ?? 'N/A' }})
                                </div>
                                <div style="margin-bottom: 15px;">
                                    <strong>Decision Date:</strong> {{ $appeal->decisionDate ? date('d M Y, h:i A', strtotime($appeal->decisionDate)) : 'N/A' }}
                                </div>
                                @if($appeal->adminRemarks)
                                    <div style="padding-top: 15px; border-top: 1px solid rgba(0,0,0,0.1);">
                                        <strong>Admin Remarks:</strong>
                                        <div style="margin-top: 8px; font-style: italic;">
                                            "{{ $appeal->adminRemarks }}"
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>
