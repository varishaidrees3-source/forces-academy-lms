<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_name = $_SESSION['student_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Forces Academy LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body style="display:block;">

<div class="dashboard-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0">Forces Academy LMS</h4>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>

    <div class="welcome-banner">
        <h2 class="m-0">Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
        <p class="m-0">Glad to have you back on your learning dashboard.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">My Courses</h5>
                    <p class="card-text text-muted">Course listing will appear here in a future update.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Notices</h5>
                    <p class="card-text text-muted">Latest announcements will appear here.</p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>