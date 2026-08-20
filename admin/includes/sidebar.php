<?php
// Expects $activePage to be set by the including file (e.g. 'dashboard', 'students', 'assignments')
$activePage = $activePage ?? '';

if (!function_exists('navLink')) {
    function navLink($page, $label, $href, $active, $icon) {
        $isActive = ($page === $active) ? 'active' : '';
        echo '<a href="' . htmlspecialchars($href) . '" class="nav-link ' . $isActive . '"><span>' . $icon . '</span> ' . htmlspecialchars($label) . '</a>';
    }
}
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <span class="sidebar-brand-label">🏫 Forces Academy <small style="opacity:.75;font-weight:500;">LMS</small></span>
        <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Close menu">&times;</button>
    </div>
    <nav class="sidebar-nav">
        <?php
        navLink('dashboard', 'Dashboard', 'dashboard.php', $activePage, '📊');
        navLink('students', 'Manage Students', 'students.php', $activePage, '👥');
        navLink('courses', 'Manage Courses', 'courses.php', $activePage, '📚');
        navLink('assignments', 'Manage Assignments', 'assignments.php', $activePage, '📝');
        navLink('results', 'Upload Results', 'results.php', $activePage, '🏆');
        navLink('notices', 'Post Notice', 'notices.php', $activePage, '📢');
        navLink('timetable', 'Manage Timetable', 'timetable.php', $activePage, '📅');
        navLink('fees', 'Manage Fees', 'fees.php', $activePage, '💰');
        ?>
        <a href="logout.php" class="nav-link logout-link mt-auto"><span>🚪</span> Logout</a>
    </nav>
</aside>
