<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

$id = intval($_GET['id'] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    header('Location: students.php');
    exit;
}

// Bonus: show this student's submissions and results too
$subStmt = mysqli_prepare($conn, "SELECT s.*, a.title FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id WHERE s.student_id = ?");
mysqli_stmt_bind_param($subStmt, 'i', $id);
mysqli_stmt_execute($subStmt);
$submissions = mysqli_stmt_get_result($subStmt);

$resStmt = mysqli_prepare($conn, "SELECT * FROM results WHERE student_id = ?");
mysqli_stmt_bind_param($resStmt, 'i', $id);
mysqli_stmt_execute($resStmt);
$results = mysqli_stmt_get_result($resStmt);

$activePage = 'students';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
<div class="row">
    <?php include 'includes/sidebar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">
        <a href="students.php" class="btn btn-sm btn-outline-secondary mb-3">&larr; Back to Students</a>
        <h2 class="mb-4"><?= htmlspecialchars($student['full_name']) ?></h2>

        <div class="card mb-4">
            <div class="card-body">
                <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
                <p><strong>Roll Number:</strong> <?= htmlspecialchars($student['roll_number']) ?></p>
                <p><strong>Class:</strong> <?= htmlspecialchars($student['class']) ?></p>
                <p><strong>Registered:</strong> <?= date('d M Y', strtotime($student['created_at'])) ?></p>
            </div>
        </div>

        <h5>Submissions</h5>
        <table class="table table-bordered bg-white mb-4">
            <thead><tr><th>Assignment</th><th>Submitted At</th><th>Status</th></tr></thead>
            <tbody>
                <?php if (mysqli_num_rows($submissions) > 0): ?>
                    <?php while ($sub = mysqli_fetch_assoc($submissions)): ?>
                        <tr>
                            <td><?= htmlspecialchars($sub['title']) ?></td>
                            <td><?= date('d M Y H:i', strtotime($sub['submitted_at'])) ?></td>
                            <td><?= htmlspecialchars($sub['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center">No submissions yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h5>Results</h5>
        <table class="table table-bordered bg-white">
            <thead><tr><th>Subject</th><th>Marks</th><th>Total</th><th>Grade</th><th>Exam Type</th></tr></thead>
            <tbody>
                <?php if (mysqli_num_rows($results) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($results)): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['subject']) ?></td>
                            <td><?= htmlspecialchars($r['marks']) ?></td>
                            <td><?= htmlspecialchars($r['total_marks']) ?></td>
                            <td><?= htmlspecialchars($r['grade']) ?></td>
                            <td><?= htmlspecialchars($r['exam_type']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No results yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</div>
</body>
</html>
