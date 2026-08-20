<?php
// Safe Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make sure student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

// Dynamic Config Path Loader
$config_path = __DIR__ . '/config/db.php';
if (!file_exists($config_path)) {
    $config_path = __DIR__ . '/../config/db.php';
}
require_once $config_path;

$student_id = $_SESSION['student_id'];

// Fetch all assignments with course name (JOIN) - Safe Query
$sql = "SELECT a.*, c.course_name AS course_name
        FROM assignments a
        LEFT JOIN courses c ON a.course_id = c.id
        ORDER BY a.due_date ASC";
$result = @$conn->query($sql);

// Fetch this student's submissions, keyed by assignment_id for quick lookup - Safe Query
$submitted = [];
$subSql = $conn->prepare("SELECT assignment_id FROM submissions WHERE student_id = ?");
if ($subSql) {
    $subSql->bind_param("i", $student_id);
    $subSql->execute();
    $subResult = $subSql->get_result();
    while ($row = $subResult->fetch_assoc()) {
        $submitted[$row['assignment_id']] = true;
    }
    $subSql->close();
}

// Show success message after submission (redirected back here)
$successMsg = isset($_GET['submitted']) && $_GET['submitted'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assignments — Forces Academy LMS</title>
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
        <a href="assignments.php" class="nav-link active">
            <i class="bi bi-clipboard-check"></i> Assignments
        </a>
        <a href="results.php" class="nav-link">
            <i class="bi bi-bar-chart"></i> My Results
        </a>
        <a href="notices.php" class="nav-link">
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
        <span class="navbar-brand mb-0 h5">Assignments</span>
    </nav>

    <div class="content-wrapper">
        <h4 class="fw-bold mb-4">📚 Assignments</h4>

        <?php if ($successMsg): ?>
            <div class="alert alert-success">Assignment submitted successfully!</div>
        <?php endif; ?>

        <?php if (!$result || $result->num_rows === 0): ?>
            <div class="text-center py-5">
                <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                <p class="mt-3 text-muted">No assignments posted yet. Check back soon!</p>
            </div>
        <?php else: ?>
        <div class="row g-3">
            <?php while ($a = $result->fetch_assoc()):
                $isOverdue = strtotime($a['due_date'] ?? date('Y-m-d')) < strtotime(date('Y-m-d')) && !isset($submitted[$a['id']]);
            ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="course-card h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="course-icon">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <?php if (isset($submitted[$a['id']])): ?>
                                <span class="badge bg-success">Submitted</span>
                            <?php elseif ($isOverdue): ?>
                                <span class="badge bg-danger">Overdue</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php endif; ?>
                        </div>

                        <h5 class="fw-bold mt-3"><?= htmlspecialchars($a['title'] ?? '') ?></h5>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-book me-1"></i><?= htmlspecialchars($a['course_name'] ?? 'General') ?>
                        </p>
                        <p class="text-muted small flex-grow-1"><?= nl2br(htmlspecialchars($a['description'] ?? '')) ?></p>

                        <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                            <small class="text-danger">
                                <i class="bi bi-calendar-event me-1"></i>
                                Due: <?= isset($a['due_date']) ? date('d M Y', strtotime($a['due_date'])) : 'N/A' ?>
                            </small>

                            <?php if (!isset($submitted[$a['id']])): ?>
                                <button class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#submitModal<?= $a['id'] ?>">
                                    Submit
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!isset($submitted[$a['id']])): ?>
                <!-- Upload Modal -->
                <div class="modal fade" id="submitModal<?= $a['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form action="submit_assignment.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                          <h5 class="modal-title">Submit: <?= htmlspecialchars($a['title'] ?? '') ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
                            <label class="form-label">Upload PDF or Image</label>
                            <input type="file" name="submission_file" class="form-control"
                                   accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Upload</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>