<?php
session_start();
require 'config/db.php'; // your existing mysqli connection file — must define $conn
 
// Make sure student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
$student_id = $_SESSION['student_id'];
 
// Fetch all assignments with course name (JOIN)
$sql = "SELECT a.*, c.course_name AS course_name
        FROM assignments a
        LEFT JOIN courses c ON a.course_id = c.id
        ORDER BY a.due_date ASC";
$result = $conn->query($sql);
 
// Fetch this student's submissions, keyed by assignment_id for quick lookup
$submitted = [];
$subSql = $conn->prepare("SELECT assignment_id FROM submissions WHERE student_id = ?");
$subSql->bind_param("i", $student_id);
$subSql->execute();
$subResult = $subSql->get_result();
while ($row = $subResult->fetch_assoc()) {
    $submitted[$row['assignment_id']] = true;
}
 
// Show success message after submission (redirected back here)
$successMsg = isset($_GET['submitted']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assignments</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">📚 Assignments</h2>
 
    <?php if ($successMsg): ?>
        <div class="alert alert-success">Assignment submitted successfully!</div>
    <?php endif; ?>
 
    <div class="row g-4">
        <?php while ($a = $result->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($a['title']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <?= htmlspecialchars($a['course_name'] ?? 'General') ?>
                        </h6>
                        <p class="card-text flex-grow-1"><?= nl2br(htmlspecialchars($a['description'])) ?></p>
                        <p class="text-danger mb-2">
                            <strong>Due:</strong> <?= date('d M Y', strtotime($a['due_date'])) ?>
                        </p>
 
                        <?php if (isset($submitted[$a['id']])): ?>
                            <span class="badge bg-success align-self-start">Submitted</span>
                        <?php else: ?>
                            <button class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#submitModal<?= $a['id'] ?>">
                                Submit Assignment
                            </button>
 
                            <!-- Upload Modal -->
                            <div class="modal fade" id="submitModal<?= $a['id'] ?>" tabindex="-1">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <form action="submit_assignment.php" method="POST" enctype="multipart/form-data">
                                    <div class="modal-header">
                                      <h5 class="modal-title">Submit: <?= htmlspecialchars($a['title']) ?></h5>
                                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
                                        <label class="form-label">Upload PDF or Image</label>
                                        <input type="file" name="submission_file" class="form-control"
                                               accept=".pdf,.jpg,.jpeg,.png" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Upload</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
 