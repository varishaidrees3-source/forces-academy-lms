<?php
require_once 'includes/admin_auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'] === 'paid' ? 'paid' : 'pending';

    if ($status === 'paid') {
        $stmt = mysqli_prepare($conn, "UPDATE fees SET status = 'paid', paid_date = CURDATE() WHERE id = ?");
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE fees SET status = 'pending', paid_date = NULL WHERE id = ?");
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    header('Location: fees.php?updated=1');
    exit;
} else {
    header('Location: fees.php');
    exit;
}
