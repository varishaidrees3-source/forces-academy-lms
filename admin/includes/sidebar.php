<?php
// Expects $activePage to be set by the including file (e.g. 'dashboard', 'students', ...)
$activePage = $activePage ?? '';

function navLink($page, $label, $href, $active, $icon) {
    $isActive = ($page === $active) ? 'active' : '';
    echo '<a href="' . $href . '" class="nav-link ' . $isActive . '"><span class="nav-icon"><i class="' . $icon . ' fa-fw"></i></span><span class="nav-text">' . $label . '</span></a>';
}
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <span class="nav-icon"><i class="fa-solid fa-graduation-cap fa-fw"></i></span>
        <div class="brand-text">
            <span class="brand-title">Forces Academy</span>
            <span class="brand-subtitle">Admin Panel</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <?php
        navLink('dashboard', 'Dashboard', 'dashboard.php', $activePage, 'fa-solid fa-gauge-high');
        navLink('students', 'Manage Students', 'students.php', $activePage, 'fa-solid fa-user-graduate');
        navLink('courses', 'Manage Courses', 'courses.php', $activePage, 'fa-solid fa-book-open');
        navLink('assignments', 'Manage Assignments', 'assignments.php', $activePage, 'fa-solid fa-file-lines');
        navLink('results', 'Upload Results', 'results.php', $activePage, 'fa-solid fa-trophy');
        navLink('notices', 'Post Notice', 'notices.php', $activePage, 'fa-solid fa-bullhorn');
        navLink('timetable', 'Manage Timetable', 'timetable.php', $activePage, 'fa-solid fa-calendar-days');
        navLink('fees', 'Manage Fees', 'fees.php', $activePage, 'fa-solid fa-money-bill-wave');
        ?>
        <a href="logout.php" class="nav-link logout-link"><span class="nav-icon"><i class="fa-solid fa-right-from-bracket fa-fw"></i></span><span class="nav-text">Logout</span></a>
    </nav>
</div>
