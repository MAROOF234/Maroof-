<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit;
}

// Get the current page's required role
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', $current_path);
$required_role = $path_parts[count($path_parts) - 2]; // Get the directory name

// Check if user has the required role
if ($_SESSION['role'] !== $required_role) {
    header('Location: ../login.php');
    exit;
}
