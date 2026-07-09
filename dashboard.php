<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/db.php';

$student_name = $_SESSION['student_name'];

// Total courses count
$courses_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM courses");
$courses_row = mysqli_fetch_assoc($courses_result);
$total_courses = $courses_row['total'];

// Latest notice
$notice_result = mysqli_query($conn, "SELECT title FROM notices ORDER BY created_at DESC LIMIT 1");
$latest_notice = mysqli_fetch_assoc($notice_result);

// Last 3 notices
$notices_result = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Forces Academy LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="dashboard-body">

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-mortarboard-fill"></i>
        <span>Forces Academy</span>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-link active">
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
        <a href="logout.php" class="nav-link logout-link">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar (Mobile) -->
    <nav class="navbar navbar-light bg-white border-bottom d-lg-none px-3">
        <button class="btn" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>
        <span class="navbar-brand mb-0 h5">Forces Academy</span>
    </nav>

    <div class="content-wrapper">
        <!-- Welcome -->
        <div class="welcome-banner mb-4">
            <h4 class="mb-1">Hello, <?php echo htmlspecialchars($student_name); ?>! 👋</h4>
            <p class="mb-0">Welcome back to Forces Academy LMS. Here's your summary.</p>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-soft">
                        <i class="bi bi-book text-primary"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $total_courses; ?></div>
                        <div class="stat-label">Total Courses</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-soft">
                        <i class="bi bi-clipboard text-warning"></i>
                    </div>
                    <div>
                        <div class="stat-number">2</div>
                        <div class="stat-label">Pending Assignments</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success-soft">
                        <i class="bi bi-bell text-success"></i>
                    </div>
                    <div>
                        <div class="stat-number notice-text">
                            <?php echo $latest_notice ? htmlspecialchars(substr($latest_notice['title'], 0, 20)).'...' : 'No notices'; ?>
                        </div>
                        <div class="stat-label">Latest Notice</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="mb-4">
            <a href="courses.php" class="btn btn-primary me-2 mb-2">
                <i class="bi bi-book me-1"></i> My Courses
            </a>
            <a href="assignments.php" class="btn btn-outline-primary mb-2">
                <i class="bi bi-clipboard me-1"></i> Assignments
            </a>
        </div>

        <!-- Recent Notices -->
        <h5 class="fw-bold mb-3">Recent Notices</h5>
        <?php while($notice = mysqli_fetch_assoc($notices_result)): ?>
        <div class="notice-card mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($notice['title']); ?></h6>
                <span class="badge bg-primary">New</span>
            </div>
            <p class="mb-1 text-muted small"><?php echo htmlspecialchars($notice['content']); ?></p>
            <small class="text-muted">
                <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($notice['posted_by']); ?> &nbsp;|&nbsp;
                <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($notice['created_at'])); ?>
            </small>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mobile sidebar toggle
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('show');
});
</script>
</body>
</html>