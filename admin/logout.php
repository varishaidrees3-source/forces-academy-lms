<?php
session_start();
// Only clear admin-related session keys so this never touches a student session
unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_role']);
session_destroy();
header('Location: login.php');
exit;
