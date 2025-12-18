<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
    <link rel="stylesheet" href="{{ asset('css/common/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/userManagement.css') }}">
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
            <div class="list-container">
                <div class="list-header">
                    <h2>User Management</h2>
                    <a href="{{ route('admin.user.create') }}" class="btn-add">+ Add New User</a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                <div class="batch-actions" id="batchActionsContainer" style="display: none; margin-bottom: 1.5rem; padding: 1rem; background: #f0f4ff; border: 1px solid #4f46e5; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <span id="selectedCount" style="font-weight: 600; color: #4f46e5;">0 users selected</span>
                        <button type="button" id="selectAllBtn" class="btn-select-all">Select All</button>
                        <button type="button" id="deselectAllBtn" class="btn-deselect-all">Deselect All</button>
                        <button type="button" id="batchBanBtn" class="btn-batch-ban">Ban Selected</button>
                        <button type="button" id="batchUnbanBtn" class="btn-batch-unban">Unban Selected</button>
                    </div>
                </div>

                <form id="batchActionForm" method="POST" action="{{ route('admin.user.batchBan') }}" style="display: none;">
                    @csrf
                    <input type="hidden" name="userIds" id="batchUserIds">
                    <input type="hidden" name="action" id="batchAction">
                </form>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" id="selectAllCheckbox" style="cursor: pointer;"></th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="user-row" data-user-id="{{ $user->userId }}">
                                <td style="width: 30px;"><input type="checkbox" class="user-checkbox" data-user-id="{{ $user->userId }}" style="cursor: pointer;"></td>
                                <td><strong>{{ $user->userId }}</strong></td>
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
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.user.show', $user->userId) }}" class="btn-view">View</a>
                                        <a href="{{ route('admin.user.edit', $user->userId) }}" class="btn-edit">Edit</a>
                                        @php
                                            $confirmMessage = $user->status === 'active' ? 'Are you sure you want to ban this user?' : 'Are you sure you want to unban this user?';
                                        @endphp
                                        <form method="POST" action="{{ route('admin.user.delete', $user->userId) }}" style="display: inline;" onsubmit="return confirm('{{ $confirmMessage }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete">
                                                {{ $user->status === 'active' ? 'Ban' : 'Unban' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($users->count() > 0)
                    <div class="pagination-container">
                        {{ $users->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const userCheckboxes = document.querySelectorAll('.user-checkbox');
            const batchActionsContainer = document.getElementById('batchActionsContainer');
            const selectedCountSpan = document.getElementById('selectedCount');
            const selectAllBtn = document.getElementById('selectAllBtn');
            const deselectAllBtn = document.getElementById('deselectAllBtn');
            const batchBanBtn = document.getElementById('batchBanBtn');
            const batchUnbanBtn = document.getElementById('batchUnbanBtn');
            const batchActionForm = document.getElementById('batchActionForm');
            const batchUserIds = document.getElementById('batchUserIds');
            const batchAction = document.getElementById('batchAction');

            // Update selected count and show/hide batch actions
            function updateBatchUI() {
                const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
                const count = selectedCheckboxes.length;
                selectedCountSpan.textContent = `${count} user(s) selected`;
                
                if (count > 0) {
                    batchActionsContainer.style.display = 'block';
                } else {
                    batchActionsContainer.style.display = 'none';
                }

                // Update select all checkbox state
                selectAllCheckbox.checked = userCheckboxes.length > 0 && 
                    document.querySelectorAll('.user-checkbox:not(:checked)').length === 0;
            }

            // Individual checkbox change
            userCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBatchUI);
            });

            // Select all checkbox
            selectAllCheckbox.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBatchUI();
            });

            // Select all button
            selectAllBtn.addEventListener('click', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                updateBatchUI();
            });

            // Deselect all button
            deselectAllBtn.addEventListener('click', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateBatchUI();
            });

            // Batch ban button
            batchBanBtn.addEventListener('click', function() {
                const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
                if (selectedCheckboxes.length === 0) {
                    alert('Please select at least one user to ban');
                    return;
                }

                if (!confirm(`Are you sure you want to ban ${selectedCheckboxes.length} user(s)?`)) {
                    return;
                }

                const userIds = Array.from(selectedCheckboxes).map(cb => cb.getAttribute('data-user-id'));
                batchUserIds.value = userIds.join(',');
                batchAction.value = 'ban';
                batchActionForm.submit();
            });

            // Batch unban button
            batchUnbanBtn.addEventListener('click', function() {
                const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
                if (selectedCheckboxes.length === 0) {
                    alert('Please select at least one user to unban');
                    return;
                }

                if (!confirm(`Are you sure you want to unban ${selectedCheckboxes.length} user(s)?`)) {
                    return;
                }

                const userIds = Array.from(selectedCheckboxes).map(cb => cb.getAttribute('data-user-id'));
                batchUserIds.value = userIds.join(',');
                batchAction.value = 'unban';
                batchActionForm.submit();
            });
        });
    </script>
