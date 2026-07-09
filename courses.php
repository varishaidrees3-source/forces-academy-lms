<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/db.php';

$courses_result = mysqli_query($conn, "SELECT * FROM courses ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses — Forces Academy LMS</title>
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
        <a href="dashboard.php" class="nav-link">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="courses.php" class="nav-link active">
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
    <nav class="navbar navbar-light bg-white border-bottom d-lg-none px-3">
        <button class="btn" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>
        <span class="navbar-brand mb-0 h5">My Courses</span>
    </nav>

    <div class="content-wrapper">
        <h4 class="fw-bold mb-4">My Courses</h4>

        <?php if (mysqli_num_rows($courses_result) === 0): ?>
            <div class="text-center py-5">
                <i class="bi bi-book fs-1 text-muted"></i>
                <p class="mt-3 text-muted">No courses available yet. Check back soon!</p>
            </div>
        <?php else: ?>
        <div class="row g-3">
            <?php while($course = mysqli_fetch_assoc($courses_result)): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="course-card h-100">
                    <div class="course-icon">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <h5 class="fw-bold mt-3"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                    <p class="text-muted small"><?php echo htmlspecialchars($course['description']); ?></p>
                    <div class="mt-auto pt-2 border-top">
                        <small class="text-muted">
                            <i class="bi bi-person me-1"></i>
                            <?php echo htmlspecialchars($course['teacher_name']); ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('show');
});
</script>
</body>
</html>