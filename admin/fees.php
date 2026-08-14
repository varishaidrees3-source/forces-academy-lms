<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

// Handle Add submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id  = intval($_POST['student_id'] ?? 0);
    $amount      = floatval($_POST['amount'] ?? 0);
    $due_date    = trim($_POST['due_date'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($student_id > 0 && $amount > 0 && $due_date !== '' && $description !== '') {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO fees (student_id, amount, due_date, status, description) VALUES (?, ?, ?, 'pending', ?)");
        mysqli_stmt_bind_param($stmt, 'idss', $student_id, $amount, $due_date, $description);
        mysqli_stmt_execute($stmt);
        header('Location: fees.php?added=1');
        exit;
    }
}

// Students list for the dropdown
$studentsResult = mysqli_query($conn, "SELECT id, full_name, roll_number, class FROM students ORDER BY full_name ASC");

// All fee records with student info
$fees = mysqli_query($conn, "
    SELECT f.*, s.full_name, s.roll_number, s.class
    FROM fees f
    JOIN students s ON f.student_id = s.id
    ORDER BY f.due_date ASC
");

$activePage = 'fees';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Fees</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
</head>
<body class="dashboard-body">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
    <div class="content-wrapper">
        <h2 class="mb-4">💰 Manage Fees</h2>

        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">Fee record added successfully.</div>
        <?php elseif (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Fee status updated.</div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Add Fee Record</h5>
                <form method="POST" action="fees.php" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select student</option>
                            <?php while ($s = mysqli_fetch_assoc($studentsResult)): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['full_name']) ?> (<?= htmlspecialchars($s['roll_number']) ?> — <?= htmlspecialchars($s['class']) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control" placeholder="e.g. 15000" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Semester 3 Tuition Fee" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Add</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($fees) > 0): ?>
                        <?php while ($f = mysqli_fetch_assoc($fees)):
                            $badge = $f['status'] === 'paid' ? 'success' : ($f['status'] === 'overdue' ? 'danger' : 'warning text-dark');
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($f['full_name']) ?> (<?= htmlspecialchars($f['roll_number']) ?>)</td>
                                <td><?= htmlspecialchars($f['class']) ?></td>
                                <td>Rs. <?= number_format($f['amount'], 2) ?></td>
                                <td><?= date('d M Y', strtotime($f['due_date'])) ?></td>
                                <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($f['status']) ?></span></td>
                                <td><?= htmlspecialchars($f['description']) ?></td>
                                <td>
                                    <?php if ($f['status'] !== 'paid'): ?>
                                    <form method="POST" action="update_fee_status.php" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                        <input type="hidden" name="status" value="paid">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Mark Paid</button>
                                    </form>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-id="<?= $f['id'] ?>"
                                            data-label="<?= htmlspecialchars($f['full_name'] . ' — ' . $f['description']) ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No fee records yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>

<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="delete_fee.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete <strong id="feeLabel"></strong>?
          <input type="hidden" name="id" id="feeIdInput">
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
    document.getElementById('feeIdInput').value = button.getAttribute('data-id');
    document.getElementById('feeLabel').textContent = button.getAttribute('data-label');
});
</script>
</body>
</html>
