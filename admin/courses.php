<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

$editCourse = null;
$successMsg = '';

// Handle Add / Update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name  = trim($_POST['course_name'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $teacher_name = trim($_POST['teacher_name'] ?? '');
    $course_id    = intval($_POST['course_id'] ?? 0);

    if ($course_name !== '' && $teacher_name !== '') {
        if ($course_id > 0) {
            // Update existing course
            $stmt = mysqli_prepare($conn,
                "UPDATE courses SET course_name = ?, description = ?, teacher_name = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'sssi', $course_name, $description, $teacher_name, $course_id);
            mysqli_stmt_execute($stmt);
            header('Location: courses.php?updated=1');
            exit;
        } else {
            // Insert new course
            $stmt = mysqli_prepare($conn,
                "INSERT INTO courses (course_name, description, teacher_name) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $course_name, $description, $teacher_name);
            mysqli_stmt_execute($stmt);
            header('Location: courses.php?added=1');
            exit;
        }
    }
}

// Load course into form for editing
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $editId);
    mysqli_stmt_execute($stmt);
    $editCourse = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$courses = mysqli_query($conn, "SELECT * FROM courses ORDER BY created_at DESC");
$activePage = 'courses';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Courses</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
<div class="row">
    <?php include 'includes/sidebar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">
        <h2 class="mb-4">📚 Manage Courses</h2>

        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">Course added successfully.</div>
        <?php elseif (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Course updated successfully.</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Course deleted successfully.</div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><?= $editCourse ? 'Edit Course' : 'Add New Course' ?></h5>
                <form method="POST" action="courses.php">
                    <input type="hidden" name="course_id" value="<?= $editCourse['id'] ?? '' ?>">
                    <div class="mb-3">
                        <label class="form-label">Course Name</label>
                        <input type="text" name="course_name" class="form-control"
                               value="<?= htmlspecialchars($editCourse['course_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($editCourse['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teacher Name</label>
                        <input type="text" name="teacher_name" class="form-control"
                               value="<?= htmlspecialchars($editCourse['teacher_name'] ?? '') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <?= $editCourse ? 'Update Course' : 'Add Course' ?>
                    </button>
                    <?php if ($editCourse): ?>
                        <a href="courses.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped bg-white">
                <thead class="table-dark">
                    <tr><th>Course Name</th><th>Description</th><th>Teacher</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($courses) > 0): ?>
                        <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['course_name']) ?></td>
                                <td><?= htmlspecialchars($c['description']) ?></td>
                                <td><?= htmlspecialchars($c['teacher_name']) ?></td>
                                <td>
                                    <a href="courses.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#deleteCourseModal"
                                            data-id="<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['course_name']) ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">No courses yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</div>

<div class="modal fade" id="deleteCourseModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="delete_course.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete <strong id="courseNameLabel"></strong>?
          <input type="hidden" name="id" id="courseIdInput">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('deleteCourseModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('courseIdInput').value = button.getAttribute('data-id');
    document.getElementById('courseNameLabel').textContent = button.getAttribute('data-name');
});
</script>
</body>
</html>
