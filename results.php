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
$rows = [];

// Prepared Statement
$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $student_id);
    if ($stmt->execute()) {
        $results = $stmt->get_result();
        if ($results) {
            $rows = $results->fetch_all(MYSQLI_ASSOC);
        }
    }
    $stmt->close();
}

$totalExams = count($rows);
$avgPercent = 0;

if ($totalExams > 0) {
    $sumPercent = 0;
    foreach ($rows as $r) {
        $totalMarks = (float)($r['total_marks'] ?? 0);
        $obtainedMarks = (float)($r['marks'] ?? 0);
        if ($totalMarks > 0) {
            $sumPercent += ($obtainedMarks / $totalMarks) * 100;
        }
    }
    $avgPercent = round($sumPercent / $totalExams, 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Results — Forces Academy LMS</title>
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
        <a href="results.php" class="nav-link active">
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
        <span class="navbar-brand mb-0 h5">My Results</span>
    </nav>

    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="fw-bold mb-0">📊 My Results</h4>
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm no-print">
                <i class="bi bi-printer me-1"></i> Print Results
            </button>
        </div>

        <!-- Quick stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-soft"><i class="bi bi-journal-check"></i></div>
                    <div>
                        <div class="stat-number"><?= $totalExams ?></div>
                        <div class="stat-label">Exams Recorded</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success-soft"><i class="bi bi-graph-up-arrow"></i></div>
                    <div>
                        <div class="stat-number"><?= $avgPercent ?>%</div>
                        <div class="stat-label">Average Score</div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($totalExams === 0): ?>
            <div class="text-center py-5">
                <i class="bi bi-bar-chart fs-1 text-muted"></i>
                <p class="mt-3 text-muted">No results available yet.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive" style="border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <table class="table table-striped table-hover align-middle bg-white mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Subject</th>
                        <th>Marks Obtained</th>
                        <th>Total Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Exam Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row):
                        $tMarks = (float)($row['total_marks'] ?? 0);
                        $oMarks = (float)($row['marks'] ?? 0);
                        $pct = $tMarks > 0 ? round(($oMarks / $tMarks) * 100, 1) : 0;
                        $grade = strtoupper($row['grade'] ?? 'N/A');
                        $badgeClass = ($grade === 'A+' || $grade === 'A') ? 'success' : ($grade === 'F' ? 'danger' : 'warning text-dark');
                    ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($row['subject'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['marks'] ?? '0') ?></td>
                            <td><?= htmlspecialchars($row['total_marks'] ?? '0') ?></td>
                            <td><?= $pct ?>%</td>
                            <td>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= htmlspecialchars($grade) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['exam_type'] ?? 'Regular') ?></td>
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
