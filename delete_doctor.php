<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';

if (empty($id)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Doctor ID is required'
    ]);
    exit();
}

// Delete doctor
if (deleteItem(DOCTORS_FILE, $id)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Doctor deleted successfully'
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting doctor'
    ]);
}
exit();
