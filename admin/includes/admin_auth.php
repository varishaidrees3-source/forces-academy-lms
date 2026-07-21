<?php
// Admin session check — include this at the very top of every protected admin page.
// This session is completely separate from the student session ($_SESSION['student_id']).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id']) || ($_SESSION['admin_role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}
