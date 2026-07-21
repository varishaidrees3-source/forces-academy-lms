<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $sql = "SELECT * FROM students WHERE full_name LIKE ? OR roll_number LIKE ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    $like = '%' . $search . '%';
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM students ORDER BY created_at DESC");
}

$deletedMsg = isset($_GET['deleted']) ? true : false;
$activePage = 'students';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Students</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
<div class="row">
    <?php include 'includes/sidebar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">
        <h2 class="mb-4">👩‍🎓 Manage Students</h2>

        <?php if ($deletedMsg): ?>
            <div class="alert alert-success">Student deleted successfully.</div>
        <?php endif; ?>

        <form method="GET" class="mb-3 d-flex" style="max-width:400px;">
            <input type="text" name="search" class="form-control me-2"
                   placeholder="Search by name or roll number"
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roll Number</th>
                        <th>Class</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($s = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['full_name']) ?></td>
                                <td><?= htmlspecialchars($s['email']) ?></td>
                                <td><?= htmlspecialchars($s['roll_number']) ?></td>
                                <td><?= htmlspecialchars($s['class']) ?></td>
                                <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                                <td>
                                    <a href="student_details.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-id="<?= $s['id'] ?>"
                                            data-name="<?= htmlspecialchars($s['full_name']) ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No students found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</div>

<!-- Shared delete confirmation modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="delete_student.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete <strong id="studentNameLabel"></strong>? This cannot be undone.
          <input type="hidden" name="id" id="studentIdInput">
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
document.getElementById('deleteModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('studentIdInput').value = button.getAttribute('data-id');
    document.getElementById('studentNameLabel').textContent = button.getAttribute('data-name');
});
</script>
</body>
</html>
