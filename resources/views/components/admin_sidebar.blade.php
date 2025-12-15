<nav id="sidebar" class="app-sidebar">
    <div class="sidebar-toggle-area">
        <button onclick="toggleSidebar()" class="sidebar-btn" title="Toggle Menu">
            <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <div class="sidebar-menu">
        <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="menu-text">Main Menu</span>
        </a>
        <a href="{{ route('admin.replacement.appeals') }}" class="menu-item {{ request()->routeIs('admin.replacement.*') ? 'active' : '' }}">
            <span class="menu-text">Replacement Management</span>
        </a>
        
        <!-- Course Subject Management Dropdown -->
        <div class="menu-dropdown {{ request()->routeIs('admin.courses') || request()->routeIs('admin.subjects') || request()->routeIs('admin.courseSubjects') ? 'active' : '' }}">
            <div class="menu-item dropdown-toggle" onclick="toggleDropdown(this)">
                <span class="menu-text">Course Subject Management</span>
                <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <div class="dropdown-menu">
                <a href="{{ route('admin.courses') }}" class="dropdown-item {{ request()->routeIs('admin.courses') ? 'active' : '' }}">
                    <span class="menu-text">Course</span>
                </a>
                <a href="{{ route('admin.subjects') }}" class="dropdown-item {{ request()->routeIs('admin.subjects') ? 'active' : '' }}">
                    <span class="menu-text">Subject</span>
                </a>
                <a href="{{ route('admin.courseSubjects') }}" class="dropdown-item {{ request()->routeIs('admin.courseSubjects') ? 'active' : '' }}">
                    <span class="menu-text">Course Subject</span>
                </a>
            </div>
        </div>
        <a href="{{ route('admin.timetableAssignment') }}" class="menu-item {{ request()->routeIs('admin.timetableAssignment') ? 'active' : '' }}">
            <span class="menu-text">Timetable Assignment</span>
        </a>
        <a href="{{ route('admin.timeslotManagement') }}" class="menu-item {{ request()->routeIs('admin.timeslotManagement') ? 'active' : '' }}">
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