// Forces Academy LMS — shared front-end behaviour

document.addEventListener('DOMContentLoaded', function () {
    var sidebar  = document.getElementById('sidebar');
    var toggle   = document.getElementById('sidebarToggle');
    var overlay  = document.getElementById('sidebarOverlay');
    var closeBtn = document.getElementById('sidebarClose');

    if (!sidebar) return;

    function openSidebar() {
        sidebar.classList.add('show');
        if (overlay) overlay.classList.add('show');
        document.body.classList.add('sidebar-open');
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
    }

    function closeSidebar() {
        sidebar.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
        document.body.classList.remove('sidebar-open');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }

    function toggleSidebar() {
        if (sidebar.classList.contains('show')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    // Hamburger button opens/closes
    if (toggle) {
        toggle.addEventListener('click', toggleSidebar);
    }

    // Dedicated X button inside the sidebar (mobile)
    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    // Tapping the dimmed area outside the sidebar closes it
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Tapping a nav link closes the sidebar (page will navigate anyway,
    // but this avoids a flash of the open sidebar on slower connections)
    sidebar.querySelectorAll('.nav-link').forEach(function (link) {
        link.addEventListener('click', closeSidebar);
    });

    // Escape key closes the sidebar
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });

    // If the viewport is resized back up to desktop width, make sure
    // the mobile-only "show" state doesn't linger
    window.addEventListener('resize', function () {
        if (window.innerWidth > 991) closeSidebar();
    });
});
