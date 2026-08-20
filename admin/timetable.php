<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class     = trim($_POST['class'] ?? '');
    $day       = trim($_POST['day'] ?? '');
    $time_slot = trim($_POST['time_slot'] ?? '');
    $subject   = trim($_POST['subject'] ?? '');
    $teacher   = trim($_POST['teacher'] ?? '');

    if ($class !== '' && $day !== '' && $time_slot !== '' && $subject !== '' && $teacher !== '') {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO timetable (class, day, time_slot, subject, teacher) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssss', $class, $day, $time_slot, $subject, $teacher);
        mysqli_stmt_execute($stmt);
        header('Location: timetable.php?added=1');
        exit;
    }
}

$entries = mysqli_query($conn, "SELECT * FROM timetable ORDER BY class, FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), time_slot");
$activePage = 'timetable';
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Timetable</title>
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
        <h2 class="mb-4">🗓️ Manage Timetable</h2>

        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">Timetable entry added successfully.</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Timetable entry deleted successfully.</div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Add Timetable Entry</h5>
                <form method="POST" action="timetable.php" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Class</label>
                        <input type="text" name="class" class="form-control" placeholder="e.g. 10-A" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Day</label>
                        <select name="day" class="form-select" required>
                            <?php foreach ($days as $d): ?>
                                <option value="<?= $d ?>"><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Time Slot</label>
                        <input type="text" name="time_slot" class="form-control" placeholder="e.g. 9:00-10:00" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Teacher</label>
                        <input type="text" name="teacher" class="form-control" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Add</button>
                    </div>
                </form>
            </div>
        </div>

        <h5 class="mb-3">All Timetable Entries</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered bg-white align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Class</th>
                        <th>Day</th>
                        <th>Time Slot</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($entries && mysqli_num_rows($entries) > 0): ?>
                        <?php while ($e = mysqli_fetch_assoc($entries)): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['class']) ?></td>
                                <td><?= htmlspecialchars($e['day']) ?></td>
                                <td><?= htmlspecialchars($e['time_slot']) ?></td>
                                <td><?= htmlspecialchars($e['subject']) ?></td>
                                <td><?= htmlspecialchars($e['teacher']) ?></td>
                                <td>
                                    <form action="delete_timetable.php" method="POST" onsubmit="return confirm('Delete this entry?');">
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No timetable entries yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
<script src="../js/main.js"></script>
</body>
</html>