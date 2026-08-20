<?php
// Safe Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/admin_auth.php';

// Dynamic Config Path Loader
$config_path = __DIR__ . '/../config/db.php';
if (!file_exists($config_path)) {
    $config_path = __DIR__ . '/config/db.php';
}
require_once $config_path;

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: students.php');
    exit;
}

// Fetch Student Profile Data
$student = null;
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            $student = mysqli_fetch_assoc($result);
        }
    }
    mysqli_stmt_close($stmt);
}

if (!$student) {
    header('Location: students.php');
    exit;
}

// Fetch Submissions safely
$submissions = [];
$subStmt = mysqli_prepare($conn, "SELECT s.*, a.title FROM submissions s 
    JOIN assignments a ON s.assignment_id = a.id WHERE s.student_id = ? ORDER BY s.submitted_at DESC");
if ($subStmt) {
    mysqli_stmt_bind_param($subStmt, 'i', $id);
    if (mysqli_stmt_execute($subStmt)) {
        $resSub = mysqli_stmt_get_result($subStmt);
        if ($resSub) {
            $submissions = mysqli_fetch_all($resSub, MYSQLI_ASSOC);
        }
    }
    mysqli_stmt_close($subStmt);
}

// Fetch Results safely
$results = [];
$resStmt = mysqli_prepare($conn, "SELECT * FROM results WHERE student_id = ?");
if ($resStmt) {
    mysqli_stmt_bind_param($resStmt, 'i', $id);
    if (mysqli_stmt_execute($resStmt)) {
        $resRes = mysqli_stmt_get_result($resStmt);
        if ($resRes) {
            $results = mysqli_fetch_all($resRes, MYSQLI_ASSOC);
        }
    }
    mysqli_stmt_close($resStmt);
}

$activePage = 'students';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Details — Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
</head>
<body class="dashboard-body">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <nav class="navbar navbar-light bg-white border-bottom d-lg-none px-3">
            <button class="btn" id="sidebarToggle" type="button" aria-label="Toggle menu" aria-expanded="false">
                <i class="bi bi-list fs-4"></i>
            </button>
            <span class="navbar-brand mb-0 h5">Forces Academy Admin</span>
        </nav>

        <div class="content-wrapper">
            <a href="students.php" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Students
            </a>
            
            <h2 class="fw-bold mb-4">👤 <?= htmlspecialchars($student['full_name'] ?? 'Student Details') ?></h2>

            <!-- Student Info Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($student['email'] ?? 'N/A') ?></p>
                            <p class="mb-2"><strong>Roll Number:</strong> <?= htmlspecialchars($student['roll_number'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Class:</strong> <?= htmlspecialchars($student['class'] ?? 'N/A') ?></p>
                            <p class="mb-0"><strong>Registered:</strong> <?= !empty($student['created_at']) ? date('d M Y', strtotime($student['created_at'])) : 'N/A' ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submissions Table -->
            <h5 class="fw-bold mb-3"><i class="bi bi-clipboard-check me-2"></i>Submissions</h5>
            <div class="table-responsive mb-4" style="border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                <table class="table table-striped table-hover align-middle bg-white mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Assignment</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($submissions)): ?>
                            <?php foreach ($submissions as $sub): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($sub['title'] ?? '') ?></td>
                                    <td><?= !empty($sub['submitted_at']) ? date('d M Y H:i', strtotime($sub['submitted_at'])) : '—' ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= htmlspecialchars(ucfirst($sub['status'] ?? 'Submitted')) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No submissions recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Results Table -->
            <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2"></i>Results</h5>
            <div class="table-responsive" style="border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                <table class="table table-striped table-hover align-middle bg-white mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Exam Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($results)): ?>
                            <?php foreach ($results as $r): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($r['subject'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($r['marks'] ?? '0') ?></td>
                                    <td><?= htmlspecialchars($r['total_marks'] ?? '0') ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= htmlspecialchars(strtoupper($r['grade'] ?? 'N/A')) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($r['exam_type'] ?? 'Regular') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No result records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/main.js"></script>
</body>
</html>