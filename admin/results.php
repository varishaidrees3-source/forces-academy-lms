<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id  = intval($_POST['student_id'] ?? 0);
    $course_id   = intval($_POST['course_id'] ?? 0);
    $subject     = trim($_POST['subject'] ?? '');
    $marks       = intval($_POST['marks'] ?? 0);
    $total_marks = intval($_POST['total_marks'] ?? 0);
    $grade       = trim($_POST['grade'] ?? '');
    $exam_type   = trim($_POST['exam_type'] ?? '');

    if ($student_id > 0 && $course_id > 0 && $subject !== '' && $grade !== '' && $exam_type !== '') {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO results (student_id, course_id, subject, marks, total_marks, grade, exam_type)
             VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'iisiiss', $student_id, $course_id, $subject, $marks, $total_marks, $grade, $exam_type);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: results.php?uploaded=1');
            exit;
        } else {
            $uploadError = 'Could not save result — please check the results table exists.';
        }
    }
}

// mysqli_query() returns false on failure (e.g. missing table/column) — guard
// every result before looping over it, so a DB hiccup shows a friendly empty
// state instead of a fatal error crashing the whole page.
$students = @mysqli_query($conn, "SELECT id, full_name, roll_number FROM students ORDER BY full_name");
$courses  = @mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name");

$recent = @mysqli_query($conn, "SELECT r.*, s.full_name, c.course_name
    FROM results r
    JOIN students s ON r.student_id = s.id
    JOIN courses c ON r.course_id = c.id
    ORDER BY r.id DESC LIMIT 10");

if (!$students) { $students = false; }
if (!$courses)  { $courses = false; }
if (!$recent)   { $recent = false; }

$activePage = 'results';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Results</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
</head>
<body class="dashboard-body">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <nav class="navbar navbar-light bg-white border-bottom d-lg-none px-3">
            <button class="btn" id="sidebarToggle" type="button" aria-label="Toggle menu" aria-expanded="false">&#9776;</button>
            <span class="navbar-brand mb-0 h5">Forces Academy Admin</span>
        </nav>
    <div class="content-wrapper">
        <h2 class="mb-4">📊 Upload Results</h2>

        <?php if (isset($_GET['uploaded'])): ?>
            <div class="alert alert-success">Result uploaded successfully.</div>
        <?php endif; ?>
        <?php if (!empty($uploadError)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($uploadError) ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" action="results.php">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Student</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">Select student</option>
                                <?php if ($students): while ($s = mysqli_fetch_assoc($students)): ?>
                                    <option value="<?= $s['id'] ?>">
                                        <?= htmlspecialchars($s['full_name']) ?> (<?= htmlspecialchars($s['roll_number']) ?>)
                                    </option>
                                <?php endwhile; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course</label>
                            <select name="course_id" class="form-select" required>
                                <option value="">Select course</option>
                                <?php if ($courses): while ($c = mysqli_fetch_assoc($courses)): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                                <?php endwhile; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Marks Obtained</label>
                            <input type="number" name="marks" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Marks</label>
                            <input type="number" name="total_marks" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grade</label>
                            <input type="text" name="grade" class="form-control" placeholder="A, B, A+..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Exam Type</label>
                            <input type="text" name="exam_type" class="form-control" placeholder="Midterm, Final..." required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Upload Result</button>
                </form>
            </div>
        </div>

        <h5 class="mb-3">Recently Uploaded</h5>
        <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead class="table-dark">
                <tr><th>Student</th><th>Course</th><th>Subject</th><th>Marks</th><th>Grade</th><th>Exam Type</th></tr>
            </thead>
            <tbody>
                <?php if ($recent && mysqli_num_rows($recent) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($recent)): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['full_name']) ?></td>
                            <td><?= htmlspecialchars($r['course_name']) ?></td>
                            <td><?= htmlspecialchars($r['subject']) ?></td>
                            <td><?= $r['marks'] ?>/<?= $r['total_marks'] ?></td>
                            <td><?= htmlspecialchars($r['grade']) ?></td>
                            <td><?= htmlspecialchars($r['exam_type']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No results uploaded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
    </div>
<script src="../js/main.js"></script>
</body>
</html>