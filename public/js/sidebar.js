function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
}

function toggleDropdown(element) {
    const dropdown = element.closest('.menu-dropdown');
    dropdown.classList.toggle('active');
}