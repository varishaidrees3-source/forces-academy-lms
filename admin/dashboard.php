<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

function countRows($conn, $table) {
    $result = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM $table");
    $row = mysqli_fetch_assoc($result);
    return $row['cnt'] ?? 0;
}

$totalStudents    = countRows($conn, 'students');
$totalCourses     = countRows($conn, 'courses');
$totalAssignments = countRows($conn, 'assignments');
$totalNotices     = countRows($conn, 'notices');

$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
<div class="row">
    <?php include 'includes/sidebar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">
        <h2 class="mb-1">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?> 👋</h2>
        <p class="text-muted mb-4">Here's an overview of the system.</p>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="card text-white bg-primary shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Total Students</h6>
                        <h2><?= $totalStudents ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Total Courses</h6>
                        <h2><?= $totalCourses ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Total Assignments</h6>
                        <h2><?= $totalAssignments ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Total Notices</h6>
                        <h2><?= $totalNotices ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="students.php" class="btn btn-outline-primary me-2">Manage Students</a>
            <a href="courses.php" class="btn btn-outline-success me-2">Manage Courses</a>
            <a href="results.php" class="btn btn-outline-warning me-2">Upload Results</a>
            <a href="notices.php" class="btn btn-outline-info">Post Notice</a>
        </div>
    </main>
</div>
</div>
</body>
</html>
