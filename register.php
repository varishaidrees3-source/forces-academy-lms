<?php
require_once 'config/db.php';
session_start();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = trim($_POST['full_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';
    $roll_number = trim($_POST['roll_number'] ?? '');
    $class       = trim($_POST['class'] ?? '');

    if (empty($full_name) || empty($email) || empty($password)
        || empty($roll_number) || empty($class)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql  = "INSERT INTO students
                 (full_name, email, password, roll_number, class)
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssss',
            $full_name, $email, $hashed, $roll_number, $class);

        if (mysqli_stmt_execute($stmt)) {
            header('Location: login.php?registered=1');
            exit;
        } else {
            $error = 'Registration failed. Email or roll number may already be in use.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Forces Academy LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-card">
    <h2>Create Account</h2>
    <p class="subtitle">Forces Academy LMS — Student Registration</p>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>
        <div class="mb-3">
            <label for="full_name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="full_name" name="full_name"
                   value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="6">
            </div>
            <div class="col-md-6 mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="roll_number" class="form-label">Roll Number</label>
                <input type="text" class="form-control" id="roll_number" name="roll_number"
                       value="<?php echo htmlspecialchars($_POST['roll_number'] ?? ''); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class"
                       value="<?php echo htmlspecialchars($_POST['class'] ?? ''); ?>" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-2">Register</button>
    </form>

    <p class="text-center mt-3 mb-0">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

</body>
</html>