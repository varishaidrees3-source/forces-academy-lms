<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['submission_file'])) {

    $assignment_id = intval($_POST['assignment_id']);
    $file = $_FILES['submission_file'];

    // --- Validate file type (PDF and images only) ---
    $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
    $allowed_mime = ['application/pdf', 'image/jpeg', 'image/png'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);

    if (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) {
        die('Invalid file type. Only PDF, JPG, and PNG are allowed. <a href="assignments.php">Go back</a>');
    }

    // --- Check upload errors ---
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('File upload failed. Please try again.');
    }

    // --- Create uploads folder if it doesn't exist ---
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // --- Generate a unique filename to avoid overwriting ---
    $uniqueName = uniqid('sub_') . '_' . $student_id . '.' . $ext;
    $destination = $uploadDir . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {

        // Store relative path in DB (not the full server path)
        $relativePath = 'uploads/' . $uniqueName;

        $stmt = $conn->prepare(
            "INSERT INTO submissions (assignment_id, student_id, file_path, status)
             VALUES (?, ?, ?, 'submitted')"
        );
        $stmt->bind_param("iis", $assignment_id, $student_id, $relativePath);

        if ($stmt->execute()) {
            header('Location: assignments.php?submitted=1');
            exit;
        } else {
            die('Database error: could not save submission.');
        }
    } else {
        die('Could not move uploaded file.');
    }
} else {
    header('Location: assignments.php');
    exit;
}
