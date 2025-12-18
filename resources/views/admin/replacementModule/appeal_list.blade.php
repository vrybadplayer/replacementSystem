<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Replacement Appeals</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/admin_appeal_list.css') }}">
</head>
<body>
    <x-header title="Admin Dashboard" />

    <div class="app-container">
        <x-admin_sidebar />

        <main class="main-content">
            <div class="appeals-admin-container">
                <div class="page-header">
                    <h2>Replacement Appeals Management</h2>
                    <p style="color: #7f8c8d;">Review and manage student replacement class requests.</p>
                </div>

                @if(session('success'))
                    <div class="success-message">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                <div class="stats-grid">
                    <div class="stat-card total">
                        <div class="stat-number">{{ $stats['total'] }}</div>
                        <div class="stat-label">Total Appeals</div>
                    </div>
                    <div class="stat-card pending">
                        <div class="stat-number">{{ $stats['pending'] }}</div>
                        <div class="stat-label">Pending Review</div>
                    </div>
                    <div class="stat-card approved">
                        <div class="stat-number">{{ $stats['approved'] }}</div>
                        <div class="stat-label">Approved</div>
                    </div>
                    <div class="stat-card rejected">
                        <div class="stat-number">{{ $stats['rejected'] }}</div>
                        <div class="stat-label">Rejected</div>
                    </div>
                </div>

                <div class="filter-section">
                    <label for="statusFilter">Filter by Status:</label>
                    <select id="statusFilter" onchange="window.location.href='?status=' + this.value">
                        <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>All Appeals</option>
                        <option value="Pending" {{ $filterStatus === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ $filterStatus === 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ $filterStatus === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="appeals-table-container">
                    @if($appeals->count() > 0)
                        <table>
                            <thead>
                                <tr>
                                    <th>Appeal ID</th>
                                    <th>Student</th>
                                    <th>Original Class</th>
                                    <th>Replacement Class</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($appeals as $appeal)
                                    <tr>
                                        <td><strong>#{{ $appeal->appealId }}</strong></td>
                                        <td>
                                            <div>{{ $appeal->user->name }}</div>
                                            <div style="font-size: 12px; color: #7f8c8d;">
                                                {{ $appeal->user->userId }}
                                            </div>
                                        </td>
                                        <td>
                                            <div><strong>{{ $appeal->originalClass->subject->subjectId ?? 'N/A' }}</strong></div>
                                            <div style="font-size: 12px; color: #555;">
                                                {{ $appeal->originalClass->date ? date('D, d M Y', strtotime($appeal->originalClass->date)) : 'N/A' }}
                                            </div>
                                            <div style="font-size: 12px; color: #555;">
                                                {{ $appeal->originalClass->startTime ?? 'N/A' }} - {{ $appeal->originalClass->endTime ?? 'N/A' }}
                                            </div>
                                            <div style="font-size: 12px; color: #555;">
                                                Group {{ $appeal->originalClass->classGroup->classGroupId ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div><strong>{{ $appeal->requestedClass->subject->subjectId ?? 'N/A' }}</strong></div>
                                            <div style="font-size: 12px; color: #555;">
                                                {{ $appeal->requestedClass->date ? date('D, d M Y', strtotime($appeal->requestedClass->date)) : 'N/A' }}
                                            </div>
                                            <div style="font-size: 12px; color: #555;">
                                                {{ $appeal->requestedClass->startTime ?? 'N/A' }} - {{ $appeal->requestedClass->endTime ?? 'N/A' }}
                                            </div>
                                            <div style="font-size: 12px; color: #555;">
                                                Group {{ $appeal->requestedClass->classGroup->classGroupId ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            {{ date('d M Y', strtotime($appeal->submissionDate)) }}<br>
                                            <span style="font-size: 12px; color: #7f8c8d;">
                                                {{ date('h:i A', strtotime($appeal->submissionDate)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ strtolower($appeal->status) }}">
                                                {{ $appeal->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.replacement.detail', $appeal->appealId) }}" class="btn-view">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="no-appeals">
                            <h3>No appeals found</h3>
                            <p>There are no appeals matching the selected filter.</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>
