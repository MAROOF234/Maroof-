<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "hospital_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $message = $conn->real_escape_string($_POST['message']);
    $created_at = date('Y-m-d H:i:s');

    // Insert into database
    $sql = "INSERT INTO contact_messages (name, email, message, created_at) 
            VALUES ('$name', '$email', '$message', '$created_at')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Thank you for your message. We will get back to you soon!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Sorry, there was an error sending your message. Please try again.";
        $_SESSION['message_type'] = "error";
    }

    $conn->close();
    header("Location: index.php#contact");
    exit();
}
