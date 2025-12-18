<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Statistics - Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .statistics-container {
            padding: 2rem;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .stats-header {
            margin-bottom: 2rem;
        }

        .stats-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .kpi-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .kpi-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .kpi-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
        }

        .kpi-card.active .kpi-value {
            color: #28a745;
        }

        .kpi-card.inactive .kpi-value {
            color: #ffc107;
        }

        .kpi-card.banned .kpi-value {
            color: #dc3545;
        }

        .kpi-card.student .kpi-value {
            color: #17a2b8;
        }

        .kpi-card.lecturer .kpi-value {
            color: #6f42c1;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .chart-card h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 1rem;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .chart-container.full-height {
            height: 400px;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: 1;
            }

            .kpi-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <x-header title="User Statistics Dashboard" />

    <div class="app-container">
        <x-admin_sidebar />

        <main class="main-content">
            <div class="statistics-container">
                <div class="stats-header">
                    <a href="{{ route('admin.user.index') }}" class="back-link">← Back to User Management</a>
                    <h1>📊 User Statistics Dashboard</h1>
                    <p style="color: #666;">Overview of user metrics and trends</p>
                </div>

                <!-- KPI Cards -->
                <div class="kpi-cards">
                    <div class="kpi-card">
                        <h3>Total Users</h3>
                        <div class="kpi-value">{{ $totalUsers }}</div>
                    </div>

                    <div class="kpi-card student">
                        <h3>Students</h3>
                        <div class="kpi-value">{{ $studentCount }}</div>
                    </div>

                    <div class="kpi-card lecturer">
                        <h3>Lecturers</h3>
                        <div class="kpi-value">{{ $lecturerCount }}</div>
                    </div>

                    <div class="kpi-card active">
                        <h3>Active Users</h3>
                        <div class="kpi-value">{{ $activeCount }}</div>
                    </div>

                    <div class="kpi-card inactive">
                        <h3>Inactive Users</h3>
                        <div class="kpi-value">{{ $inactiveCount }}</div>
                    </div>

                    <div class="kpi-card banned">
                        <h3>Banned Users</h3>
                        <div class="kpi-value">{{ $bannedCount }}</div>
                    </div>

                    <div class="kpi-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h3 style="color: rgba(255, 255, 255, 0.9);">New This Month</h3>
                        <div class="kpi-value" style="color: white;">{{ $newUsersThisMonth }}</div>
                    </div>

                    <div class="kpi-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                        <h3 style="color: rgba(255, 255, 255, 0.9);">Recently Active</h3>
                        <div class="kpi-value" style="color: white;">{{ $recentlyActive }}</div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="charts-grid">
                    <!-- User Role Distribution (Pie Chart) -->
                    <div class="chart-card">
                        <h3>User Role Distribution</h3>
                        <div class="chart-container">
                            <canvas id="roleChart"></canvas>
                        </div>
                    </div>

                    <!-- User Status Distribution (Doughnut Chart) -->
                    <div class="chart-card">
                        <h3>User Status Distribution</h3>
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>

                    <!-- Monthly User Creation Trend (Line Chart) -->
                    <div class="chart-card full-width">
                        <h3>User Creation Trend (Last 12 Months)</h3>
                        <div class="chart-container full-height">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        // Chart.js configurations
        const chartDefaults = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                    }
                }
            }
        };

        // 1. Role Distribution Chart
        const roleCtx = document.getElementById('roleChart').getContext('2d');
        new Chart(roleCtx, {
            type: 'pie',
            data: {
                labels: ['Students', 'Lecturers'],
                datasets: [{
                    data: [{{ $studentCount }}, {{ $lecturerCount }}],
                    backgroundColor: [
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(111, 66, 193, 0.8)',
                    ],
                    borderColor: [
                        'rgba(23, 162, 184, 1)',
                        'rgba(111, 66, 193, 1)',
                    ],
                    borderWidth: 2,
                }]
            },
            options: chartDefaults
        });

        // 2. Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive', 'Banned'],
                datasets: [{
                    data: [{{ $activeCount }}, {{ $inactiveCount }}, {{ $bannedCount }}],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)',
                    ],
                    borderWidth: 2,
                }]
            },
            options: chartDefaults
        });

        // 3. Monthly Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($monthlyTrend as $trend)
                        '{{ $trend['month'] }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'New Users',
                    data: [
                        @foreach($monthlyTrend as $trend)
                            {{ $trend['count'] }},
                        @endforeach
                    ],
                    borderColor: 'rgba(0, 123, 255, 1)',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(0, 123, 255, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                ...chartDefaults,
                plugins: {
                    ...chartDefaults.plugins,
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                        }
                    }
                }
            }
        });

        </script>
</body>
</html>
