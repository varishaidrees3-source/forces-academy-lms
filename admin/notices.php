<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title !== '' && $content !== '') {
        $postedBy = $_SESSION['admin_username'];
        $stmt = mysqli_prepare($conn, "INSERT INTO notices (title, content, posted_by) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $title, $content, $postedBy);
        mysqli_stmt_execute($stmt);
        header('Location: notices.php?posted=1');
        exit;
    }
}

$notices = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC");
$activePage = 'notices';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Post Notice</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
<div class="row">
    <?php include 'includes/sidebar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">
        <h2 class="mb-4">📢 Post Notice</h2>

        <?php if (isset($_GET['posted'])): ?>
            <div class="alert alert-success">Notice posted successfully.</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Notice deleted successfully.</div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">New Notice</h5>
                <form method="POST" action="notices.php">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Post Notice</button>
                </form>
            </div>
        </div>

        <h5 class="mb-3">All Notices</h5>
        <?php if (mysqli_num_rows($notices) > 0): ?>
            <?php while ($n = mysqli_fetch_assoc($notices)): ?>
                <div class="alert alert-secondary d-flex justify-content-between align-items-start">
                    <div>
                        <strong><?= htmlspecialchars($n['title']) ?></strong>
                        <p class="mb-1"><?= nl2br(htmlspecialchars($n['content'])) ?></p>
                        <small class="text-muted">
                            Posted by <?= htmlspecialchars($n['posted_by']) ?> on
                            <?= date('d M Y', strtotime($n['created_at'])) ?>
                        </small>
                    </div>
                    <form action="delete_notice.php" method="POST" onsubmit="return confirm('Delete this notice?');">
                        <input type="hidden" name="id" value="<?= $n['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted">No notices posted yet.</p>
        <?php endif; ?>
    </main>
</div>
</div>
</body>
</html>
