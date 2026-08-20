<?php
// Safe Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

// Config Path Check (Dynamic Loader)
$config_path = __DIR__ . '/config/db.php';
if (!file_exists($config_path)) {
    $config_path = __DIR__ . '/../config/db.php';
}
require_once $config_path;

$student_id = $_SESSION['student_id'];
$class = '';

// Get this student's class safely
$stmt = mysqli_prepare($conn, "SELECT class FROM students WHERE id = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
        $student = mysqli_fetch_assoc($res);
        $class = $student['class'] ?? '';
    }
    mysqli_stmt_close($stmt);
}

// Pull all timetable rows for this class
$grid = [];
$timeSlots = [];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

if ($class !== '') {
    $stmt2 = mysqli_prepare($conn, "SELECT * FROM timetable WHERE class = ?");
    if ($stmt2) {
        mysqli_stmt_bind_param($stmt2, 's', $class);
        mysqli_stmt_execute($stmt2);
        $rows = mysqli_stmt_get_result($stmt2);
        
        if ($rows) {
            while ($r = mysqli_fetch_assoc($rows)) {
                $slot = $r['time_slot'] ?? '';
                $day = $r['day'] ?? '';
                if ($slot !== '' && $day !== '') {
                    $grid[$slot][$day] = $r;
                    if (!in_array($slot, $timeSlots)) {
                        $timeSlots[] = $slot;
                    }
                }
            }
        }
        mysqli_stmt_close($stmt2);
    }
}

// Natural sort for proper chronological time slots ordering
natsort($timeSlots);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Timetable — Forces Academy LMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body class="dashboard-body">

<!-- Sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <span class="sidebar-brand-label"><i class="bi bi-mortarboard-fill"></i> Forces Academy</span>
        <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Close menu">&times;</button>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-link">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="courses.php" class="nav-link">
            <i class="bi bi-book"></i> My Courses
        </a>
        <a href="assignments.php" class="nav-link">
            <i class="bi bi-clipboard-check"></i> Assignments
        </a>
        <a href="results.php" class="nav-link">
            <i class="bi bi-bar-chart"></i> My Results
        </a>
        <a href="notices.php" class="nav-link">
            <i class="bi bi-bell"></i> Notices
        </a>
        <a href="timetable.php" class="nav-link active">
            <i class="bi bi-calendar-week"></i> Timetable
        </a>
        <a href="fees.php" class="nav-link">
            <i class="bi bi-cash-coin"></i> My Fees
        </a>
        <a href="profile.php" class="nav-link">
            <i class="bi bi-person-circle"></i> My Profile
        </a>
        <a href="logout.php" class="nav-link logout-link">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">
    <nav class="navbar navbar-light bg-white border-bottom d-lg-none px-3">
        <button class="btn" id="sidebarToggle" aria-label="Toggle menu" aria-expanded="false">
            <i class="bi bi-list fs-4"></i>
        </button>
        <span class="navbar-brand mb-0 h5">Timetable</span>
    </nav>

    <div class="content-wrapper">
        <h4 class="fw-bold mb-1">🗓️ Weekly Timetable</h4>
        <p class="text-muted mb-4">Class: <strong><?= htmlspecialchars($class ?: 'Not Assigned') ?></strong></p>

        <?php if (empty($timeSlots)): ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                <p class="mt-3 text-muted">No timetable published yet for your class.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive" style="border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <table class="table table-bordered bg-white mb-0 text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Time</th>
                        <?php foreach ($days as $d): ?>
                            <th><?= $d ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($timeSlots as $slot): ?>
                        <tr>
                            <td class="fw-semibold table-light"><?= htmlspecialchars($slot) ?></td>
                            <?php foreach ($days as $d): ?>
                                <td>
                                    <?php if (isset($grid[$slot][$d])): ?>
                                        <div class="fw-semibold"><?= htmlspecialchars($grid[$slot][$d]['subject'] ?? '') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($grid[$slot][$d]['teacher'] ?? '') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
