<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $fullName = $_POST['fullName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    // Validate required fields
    if (!$username || !$password || !$confirmPassword || !$fullName || !$email || !$phone) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit();
    }

    // Validate password match
    if ($password !== $confirmPassword) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Passwords do not match'
        ]);
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit();
    }

    // Check if username already exists
    $users = readJsonFile(USERS_FILE);
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Username already exists'
            ]);
            exit();
        }
    }

    // Create new user
    $newUser = [
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'fullName' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'role' => 'patient'
    ];

    // Add user to database
    if (addItem(USERS_FILE, $newUser)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error creating user'
        ]);
    }
    exit();
}

// If not a POST request, redirect to registration page
header('Location: register.html');
exit();
