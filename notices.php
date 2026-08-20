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

$search = trim($_GET['search'] ?? '');
$notices_result = false;

if ($search !== '') {
    $stmt = mysqli_prepare($conn, "SELECT * FROM notices WHERE title LIKE ? ORDER BY created_at DESC");
    if ($stmt) {
        $like = '%' . $search . '%';
        mysqli_stmt_bind_param($stmt, 's', $like);
        mysqli_stmt_execute($stmt);
        $notices_result = mysqli_stmt_get_result($stmt);
    }
} else {
    $notices_result = @mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC");
}

$has_notices = $notices_result && mysqli_num_rows($notices_result) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices — Forces Academy LMS</title>
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
        <a href="notices.php" class="nav-link active">
            <i class="bi bi-bell"></i> Notices
        </a>
        <a href="timetable.php" class="nav-link">
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
        <span class="navbar-brand mb-0 h5">Notices</span>
    </nav>

    <div class="content-wrapper">
        <h4 class="fw-bold mb-4">Notice Board</h4>

        <form method="GET" class="mb-4 d-flex" style="max-width:400px;">
            <input type="text" name="search" class="form-control me-2"
                   placeholder="Search notices by title..."
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
            <?php if ($search !== ''): ?>
                <a href="notices.php" class="btn btn-outline-secondary ms-2">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (!$has_notices): ?>
            <div class="text-center py-5">
                <i class="bi bi-bell-slash fs-1 text-muted"></i>
                <p class="mt-3 text-muted"><?php echo $search !== '' ? 'No notices match your search.' : 'No notices posted yet.'; ?></p>
            </div>
        <?php else: ?>
            <?php while($notice = mysqli_fetch_assoc($notices_result)): ?>
            <div class="notice-card mb-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($notice['title'] ?? ''); ?></h5>
                    <span class="badge bg-primary ms-2">New</span>
                </div>
                <p class="mb-2"><?php echo htmlspecialchars($notice['content'] ?? ''); ?></p>
                <small class="text-muted">
                    <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($notice['posted_by'] ?? 'Admin'); ?>
                    &nbsp;|&nbsp;
                    <i class="bi bi-calendar me-1"></i><?php echo !empty($notice['created_at']) ? date('F d, Y', strtotime($notice['created_at'])) : date('F d, Y'); ?>
                </small>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>