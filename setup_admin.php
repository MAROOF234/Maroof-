<?php
require_once '../includes/db_connection.php';

// Default admin credentials
$username = 'admin';
$password = 'admin123'; // Simple password for initial setup
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND role = 'admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Admin user already exists!<br>";
        echo "Username: " . $username . "<br>";
        echo "Password: " . $password . "<br>";
        echo "<a href='login.php'>Go to Login</a>";
    } else {
        // Create new admin user
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            echo "Admin user created successfully!<br>";
            echo "Username: " . $username . "<br>";
            echo "Password: " . $password . "<br>";
            echo "<a href='login.php'>Go to Login</a>";
        } else {
            echo "Error creating admin user.";
        }
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}

$stmt->close();
$conn->close();
