<nav id="sidebar" class="app-sidebar">
    <div class="sidebar-toggle-area">
        <button onclick="toggleSidebar()" class="sidebar-btn" title="Toggle Menu">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <div class="sidebar-menu">
        <a href="{{ route('admin.dashboard') }}" class="menu-item">
            <span class="menu-text">Main Menu</span>
        </a>
        <a href="{{ route('admin.replacement.appeals') }}" class="menu-item">
            <span class="menu-text">Replacement Management</span>
        </a>
        <a href="{{ route('admin.courses') }}" class="menu-item">
            <span class="menu-text">Course Management</span>
        </a>
        <a href="{{ route('admin.subjects') }}" class="menu-item">
            <span class="menu-text">Subject Management</span>
        </a>
        <!-- Active State Styling (Hardcoded for this page) style="background-color: #d1d5db; border-right: 4px solid #3b82f6; -->
        <a href="{{ route('admin.timetableAssignment') }}" class="menu-item">
            <span class="menu-text">Timetable Assignment</span>
        </a>
        <a href="{{ route('admin.timeslotManagement') }}" class="menu-item">
            <span class="menu-text">Timeslot Management</span>
        </a>
        <a href="{{ route('admin.user.index') }}" class="menu-item {{ request()->routeIs('admin.user.*') ? 'active' : '' }}">
            <span class="menu-text">User Management</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="logout-item">
            @csrf
            <button type="submit" class="logout-btn">
                <i class='bx bx-log-out menu-icon'></i>
                <span class="menu-text">Log out</span>
            </button>
        </form>
    </div>
</nav>