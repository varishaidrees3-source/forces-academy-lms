<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/db.php';
$student_id = $_SESSION['student_id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM fees WHERE student_id = ? ORDER BY due_date ASC");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Total pending = pending + overdue (not yet paid)
$totalPending = 0;
foreach ($rows as $r) {
    if ($r['status'] !== 'paid') {
        $totalPending += $r['amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Fees — Forces Academy LMS</title>
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
        <a href="timetable.php" class="nav-link">
            <i class="bi bi-calendar-week"></i> Timetable
        </a>
        <a href="fees.php" class="nav-link active">
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
        <span class="navbar-brand mb-0 h5">My Fees</span>
    </nav>

    <div class="content-wrapper">
        <h4 class="fw-bold mb-4">💰 My Fees</h4>

        <!-- Total pending amount, prominent at top -->
        <div class="course-card mb-4" style="background:linear-gradient(135deg,#ef4444,#f97316); color:#fff;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="small text-uppercase" style="opacity:.85;">Total Pending Amount</div>
                    <div class="fw-bold" style="font-size:2rem;">Rs. <?= number_format($totalPending, 2) ?></div>
                </div>
                <i class="bi bi-exclamation-circle" style="font-size:2.5rem; opacity:.7;"></i>
            </div>
        </div>

        <?php if (count($rows) === 0): ?>
            <div class="text-center py-5">
                <i class="bi bi-cash-stack fs-1 text-muted"></i>
                <p class="mt-3 text-muted">No fee records yet.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive" style="border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
            <table class="table table-striped table-hover align-middle bg-white mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Paid Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $f):
                        $badge = $f['status'] === 'paid' ? 'success' : ($f['status'] === 'overdue' ? 'danger' : 'warning text-dark');
                    ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($f['description']) ?></td>
                            <td>Rs. <?= number_format($f['amount'], 2) ?></td>
                            <td><?= date('d M Y', strtotime($f['due_date'])) ?></td>
                            <td><?= $f['paid_date'] ? date('d M Y', strtotime($f['paid_date'])) : '—' ?></td>
                            <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($f['status']) ?></span></td>
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
