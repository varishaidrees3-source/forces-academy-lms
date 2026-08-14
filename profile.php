<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/db.php';
$student_id = $_SESSION['student_id'];

$infoMsg = '';
$infoErr = '';
$pwdMsg = '';
$pwdErr = '';

// --- Handle profile info update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    if ($full_name === '' || $email === '') {
        $infoErr = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $infoErr = 'Please enter a valid email address.';
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE students SET full_name = ?, email = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssi', $full_name, $email, $student_id);
        if (mysqli_stmt_execute($stmt)) {
            // keep session in sync with the new data
            $_SESSION['student_name'] = $full_name;
            $infoMsg = 'Profile updated successfully.';
        } else {
            $infoErr = 'Could not update profile. Email may already be in use.';
        }
    }
}

// --- Handle password change ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current  = $_POST['current_password'] ?? '';
    $new      = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT password FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$row || !password_verify($current, $row['password'])) {
        $pwdErr = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $pwdErr = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $pwdErr = 'New password and confirmation do not match.';
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = mysqli_prepare($conn, "UPDATE students SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, 'si', $hashed, $student_id);
        mysqli_stmt_execute($update);
        $pwdMsg = 'Password changed successfully.';
    }
}

// --- Always reload fresh student data for display ---
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile — Forces Academy LMS</title>
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
        <a href="fees.php" class="nav-link">
            <i class="bi bi-cash-coin"></i> My Fees
        </a>
        <a href="profile.php" class="nav-link active">
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
        <button class="btn" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>
        <span class="navbar-brand mb-0 h5">My Profile</span>
    </nav>

    <div class="content-wrapper">
        <h4 class="fw-bold mb-4">👤 My Profile</h4>

        <div class="row g-4">
            <!-- Current details + edit form -->
            <div class="col-12 col-lg-6">
                <div class="course-card h-100">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div class="course-icon" style="width:64px;height:64px;font-size:1.8rem;">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($student['full_name']) ?></h5>
                            <small class="text-muted">Roll No: <?= htmlspecialchars($student['roll_number']) ?> &middot; Class: <?= htmlspecialchars($student['class']) ?></small>
                        </div>
                    </div>

                    <?php if ($infoMsg): ?><div class="alert alert-success py-2"><?= $infoMsg ?></div><?php endif; ?>
                    <?php if ($infoErr): ?><div class="alert alert-danger py-2"><?= $infoErr ?></div><?php endif; ?>

                    <form method="POST" action="profile.php">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control"
                                   value="<?= htmlspecialchars($student['full_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($student['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Roll Number</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['roll_number']) ?>" disabled>
                            <small class="text-muted">Roll number cannot be changed.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Class</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['class']) ?>" disabled>
                        </div>
                        <button type="submit" name="update_profile" value="1" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change password -->
            <div class="col-12 col-lg-6">
                <div class="course-card h-100">
                    <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>

                    <?php if ($pwdMsg): ?><div class="alert alert-success py-2"><?= $pwdMsg ?></div><?php endif; ?>
                    <?php if ($pwdErr): ?><div class="alert alert-danger py-2"><?= $pwdErr ?></div><?php endif; ?>

                    <form method="POST" action="profile.php">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" minlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                        </div>
                        <button type="submit" name="change_password" value="1" class="btn btn-outline-primary">
                            <i class="bi bi-key me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
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
