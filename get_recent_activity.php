<?php
session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    // Get recent appointments
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.appointment_date,
            a.status,
            u1.fullName as patient_name,
            u2.fullName as doctor_name
        FROM appointments a
        JOIN users u1 ON a.patient_id = u1.id
        JOIN users u2 ON a.doctor_id = u2.id
        ORDER BY a.appointment_date DESC
        LIMIT 5
    ");
    $stmt->execute();
    $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Format activities
    $activities = [];
    foreach ($appointments as $appointment) {
        $activities[] = [
            'icon' => 'fa-calendar-check',
            'iconClass' => 'appointments-icon',
            'title' => 'New Appointment',
            'description' => "Patient {$appointment['patient_name']} scheduled with Dr. {$appointment['doctor_name']}",
            'time' => date('M d, Y H:i', strtotime($appointment['appointment_date']))
        ];
    }

    // Get recent user registrations
    $stmt = $conn->prepare("
        SELECT id, fullName, role, created_at
        FROM users
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($users as $user) {
        $activities[] = [
            'icon' => $user['role'] === 'doctor' ? 'fa-user-md' : 'fa-user',
            'iconClass' => $user['role'] === 'doctor' ? 'doctors-icon' : 'patients-icon',
            'title' => 'New ' . ucfirst($user['role']),
            'description' => "{$user['fullName']} registered as {$user['role']}",
            'time' => date('M d, Y H:i', strtotime($user['created_at']))
        ];
    }

    // Sort activities by time
    usort($activities, function ($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    // Take only the 5 most recent activities
    $activities = array_slice($activities, 0, 5);

    echo json_encode($activities);
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred']);
}

$stmt->close();
$conn->close();
