<?php
// Expects $activePage to be set by the including file (e.g. 'dashboard', 'students', ...)
$activePage = $activePage ?? '';

function navLink($page, $label, $href, $active) {
    $isActive = ($page === $active) ? 'active bg-primary text-white' : 'text-dark';
    echo '<a href="' . $href . '" class="list-group-item list-group-item-action ' . $isActive . '">' . $label . '</a>';
}
?>
<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse show" style="min-height: 100vh;">
    <div class="p-3">
        <h5 class="mb-3">⚙️ Admin Panel</h5>
        <div class="list-group">
            <?php
            navLink('dashboard', 'Dashboard', 'dashboard.php', $activePage);
            navLink('students', 'Manage Students', 'students.php', $activePage);
            navLink('courses', 'Manage Courses', 'courses.php', $activePage);
            navLink('assignments', 'Manage Assignments', 'assignments.php', $activePage);
            navLink('results', 'Upload Results', 'results.php', $activePage);
            navLink('notices', 'Post Notice', 'notices.php', $activePage);
            ?>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
        </div>
    </div>
</div>
