<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
$student_id = $_SESSION['student_id'];

// Only pull results for the logged-in student
$stmt = $conn->prepare("SELECT * FROM results WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$results = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Results</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">📊 My Results</h2>

    <div class="table-responsive">
        <table class="table table-striped table-bordered bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Subject</th>
                    <th>Marks Obtained</th>
                    <th>Total Marks</th>
                    <th>Grade</th>
                    <th>Exam Type</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results->num_rows > 0): ?>
                    <?php while ($row = $results->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['subject']) ?></td>
                            <td><?= htmlspecialchars($row['marks']) ?></td>
                            <td><?= htmlspecialchars($row['total_marks']) ?></td>
                            <td>
                                <span class="badge bg-<?= $row['grade'] === 'A+' || $row['grade'] === 'A' ? 'success' : ($row['grade'] === 'F' ? 'danger' : 'warning') ?>">
                                    <?= htmlspecialchars($row['grade']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['exam_type']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No results available yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
