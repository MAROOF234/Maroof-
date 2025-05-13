<?php
session_start();
header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];
$password = $data['password'];

// Simple hardcoded admin credentials
$admin_username = "admin";
$admin_password = "admin123";

if ($username === $admin_username && $password === $admin_password) {
    // Set session variables
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = $admin_username;
    $_SESSION['role'] = 'admin';

    echo json_encode([
        'success' => true,
        'message' => 'Login successful'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password'
    ]);
}
