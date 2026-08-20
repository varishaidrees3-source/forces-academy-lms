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

$search = trim($_GET['search'] ?? '');
$students = [];

if ($search !== '') {
    $sql = "SELECT * FROM students WHERE full_name LIKE ? OR email LIKE ? OR roll_number LIKE ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        $like = '%' . $search . '%';
        mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
        if (mysqli_stmt_execute($stmt)) {
            $res = mysqli_stmt_get_result($stmt);
            if ($res) {
                $students = mysqli_fetch_all($res, MYSQLI_ASSOC);
            }
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM students ORDER BY created_at DESC");
    if ($stmt) {
        if (mysqli_stmt_execute($stmt)) {
            $res = mysqli_stmt_get_result($stmt);
            if ($res) {
                $students = mysqli_fetch_all($res, MYSQLI_ASSOC);
            }
        }
        mysqli_stmt_close($stmt);
    }
}

$deletedMsg = isset($_GET['deleted']) ? true : false;
$activePage = 'students';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Students — Admin</title>
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
            <h2 class="fw-bold mb-4">👩‍🎓 Manage Students</h2>

            <?php if ($deletedMsg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Student record deleted successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <form method="GET" class="mb-4 d-flex gap-2" style="max-width:450px;">
                <input type="text" name="search" class="form-control"
                       placeholder="Search by name, email, or roll number..."
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                    <i class="bi bi-search"></i> Search
                </button>
                <?php if ($search !== ''): ?>
                    <a href="students.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </form>

            <!-- Students Table -->
            <div class="table-responsive" style="border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                <table class="table table-striped table-hover align-middle bg-white mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roll Number</th>
                            <th>Class</th>
                            <th>Registered</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($s['full_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($s['email'] ?? 'N/A') ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s['roll_number'] ?? 'N/A') ?></span></td>
                                    <td><?= htmlspecialchars($s['class'] ?? 'N/A') ?></td>
                                    <td><?= !empty($s['created_at']) ? date('d M Y', strtotime($s['created_at'])) : '—' ?></td>
                                    <td class="text-center">
                                        <a href="student_details.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-id="<?= (int)$s['id'] ?>"
                                                data-name="<?= htmlspecialchars($s['full_name'] ?? '') ?>">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No student records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Shared Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="delete_student.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete <strong id="studentNameLabel" class="text-danger"></strong>? This action cannot be undone.
                        <input type="hidden" name="id" id="studentIdInput">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('studentIdInput').value = button.getAttribute('data-id') || '';
            document.getElementById('studentNameLabel').textContent = button.getAttribute('data-name') || 'this student';
        });
    }
});
</script>
<script src="../js/main.js"></script>
</body>
</html>