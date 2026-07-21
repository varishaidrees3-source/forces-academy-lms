<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $course_id   = intval($_POST['course_id'] ?? 0);
    $due_date    = $_POST['due_date'] ?? '';

    if ($title !== '' && $course_id > 0 && $due_date !== '') {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO assignments (title, description, course_id, due_date) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssis', $title, $description, $course_id, $due_date);
        mysqli_stmt_execute($stmt);
        header('Location: assignments.php?added=1');
        exit;
    }
}

if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM assignments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $delId);
    mysqli_stmt_execute($stmt);
    header('Location: assignments.php?deleted=1');
    exit;
}

$courses = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name");
$assignments = mysqli_query($conn, "SELECT a.*, c.course_name FROM assignments a
    LEFT JOIN courses c ON a.course_id = c.id ORDER BY a.due_date ASC");

$activePage = 'assignments';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Assignments</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
<div class="row">
    <?php include 'includes/sidebar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">
        <h2 class="mb-4">📝 Manage Assignments</h2>

        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">Assignment added successfully.</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Assignment deleted successfully.</div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Add New Assignment</h5>
                <form method="POST" action="assignments.php">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course</label>
                            <select name="course_id" class="form-select" required>
                                <option value="">Select course</option>
                                <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Add Assignment</button>
                </form>
            </div>
        </div>

        <table class="table table-bordered table-striped bg-white">
            <thead class="table-dark">
                <tr><th>Title</th><th>Course</th><th>Due Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($assignments) > 0): ?>
                    <?php while ($a = mysqli_fetch_assoc($assignments)): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['title']) ?></td>
                            <td><?= htmlspecialchars($a['course_name'] ?? 'N/A') ?></td>
                            <td><?= date('d M Y', strtotime($a['due_date'])) ?></td>
                            <td>
                                <a href="assignments.php?delete=<?= $a['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this assignment?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No assignments yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</div>
</body>
</html>
