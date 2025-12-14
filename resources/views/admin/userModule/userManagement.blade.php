<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        
        .app-container {
            display: flex;
        }
        
        .main-content {
            flex: 1;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            color: #666;
        }
        
        tr:hover {
            background: #f9fafb;
        }
        
        .role-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .role-student {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .role-admin {
            background: #fecdca;
            color: #7f1d1d;
        }

        .role-lecturer {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-banned {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-small {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit {
            background: #3b82f6;
            color: white;
        }
        
        .btn-edit:hover {
            background: #2563eb;
        }
        
        .btn-ban {
            background: #dc3545;
            color: white;
        }
        
        .btn-ban:hover {
            background: #c82333;
        }

        .btn-unban {
            background: #28a745;
            color: white;
        }
        
        .btn-unban:hover {
            background: #218838;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .pagination-container {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pagination {
            display: flex;
            gap: 8px;
            list-style: none;
            padding: 0;
            margin: 0;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .pagination li {
            display: inline;
        }

        .pagination a, 
        .pagination span {
            padding: 8px 14px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            text-decoration: none;
            color: #666;
            transition: all 0.2s ease;
            display: inline-block;
            font-weight: 500;
            font-size: 14px;
        }

        .pagination a {
            background: white;
            cursor: pointer;
        }

        .pagination a:hover {
            background: #007bff;
            border-color: #007bff;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .pagination .active span {
            background: #007bff;
            color: white;
            border-color: #007bff;
            font-weight: 600;
        }

        .pagination .disabled span {
            color: #ccc;
            border-color: #e0e0e0;
            cursor: not-allowed;
            background: #f9f9f9;
        }

        
        @media (max-width: 768px) {
            table {
                font-size: 13px;
            }
            
            th, td {
                padding: 10px;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <x-header title="User Management">
        <div style="margin-left: auto; display: flex; align-items: center; gap: 1rem;">
            <span style="color: #666;">{{ Auth::user()->name }} (Admin)</span>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">
                    Logout
                </button>
            </form>
        </div>
    </x-header>

    <div class="app-container">
        <x-admin_sidebar />

        <main class="main-content">
            <div class="container">
                <h1>User Management</h1>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                <div class="table-container">
                    @if($users->count() > 0)
                        <table>
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->userId }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="role-badge role-{{ $user->role }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $user->status }}">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="{{ route('admin.user.edit', $user->userId) }}" class="btn-small btn-edit">Edit</a>
                                                <form method="POST" action="{{ route('admin.user.delete', $user->userId) }}" style="display: inline;" onsubmit="return confirm('{{ $user->status === 'active' ? 'Ban this user?' : 'Unban this user?' }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-small {{ $user->status === 'active' ? 'btn-ban' : 'btn-unban' }}">
                                                        {{ $user->status === 'active' ? 'Ban' : 'Unban' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <div class="pagination-container">
                            {{ $users->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="empty-state">
                            <p>No users found</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>
