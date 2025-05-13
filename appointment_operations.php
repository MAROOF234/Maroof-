<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/DataHandler.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$dataHandler = new DataHandler();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $appointmentData = [
                    'id' => $dataHandler->generateId('AP'),
                    'patient_id' => $_POST['patient_id'],
                    'doctor_id' => $_POST['doctor_id'],
                    'appointment_date' => $_POST['appointment_date'],
                    'appointment_time' => $_POST['appointment_time'],
                    'reason' => $_POST['reason'],
                    'status' => 'Scheduled',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Add to appointments.json
                $dataHandler->addAppointment($appointmentData);
                header('Location: appointments.php');
                exit;
                break;

            case 'update':
                if (isset($_POST['appointment_id'])) {
                    $appointmentData = [
                        'id' => $_POST['appointment_id'],
                        'patient_id' => $_POST['patient_id'],
                        'doctor_id' => $_POST['doctor_id'],
                        'appointment_date' => $_POST['appointment_date'],
                        'appointment_time' => $_POST['appointment_time'],
                        'reason' => $_POST['reason'],
                        'status' => $_POST['status'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $dataHandler->updateAppointment($_POST['appointment_id'], $appointmentData);
                    header('Location: appointments.php');
                    exit;
                }
                break;

            case 'delete':
                if (isset($_POST['appointment_id'])) {
                    $dataHandler->deleteAppointment($_POST['appointment_id']);
                    header('Location: appointments.php');
                    exit;
                }
                break;
        }
    }
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['get_appointment'])) {
        $appointment = $dataHandler->getAppointmentById($_GET['get_appointment']);
        if ($appointment) {
            echo json_encode($appointment);
        } else {
            echo json_encode(['error' => 'Appointment not found']);
        }
        exit;
    }
}

// Initialize appointments array if not exists in session
if (!isset($_SESSION['appointments'])) {
    $_SESSION['appointments'] = [
        [
            'id' => 'A001',
            'patient_id' => 'P001',
            'patient_name' => 'John Doe',
            'doctor_id' => 'D001',
            'doctor_name' => 'Dr. Sarah Wilson',
            'date' => '2024-03-20',
            'time' => '10:00',
            'department' => 'Cardiology',
            'status' => 'Scheduled'
        ],
        [
            'id' => 'A002',
            'patient_id' => 'P002',
            'patient_name' => 'Jane Smith',
            'doctor_id' => 'D002',
            'doctor_name' => 'Dr. Michael Brown',
            'date' => '2024-03-21',
            'time' => '14:30',
            'department' => 'Neurology',
            'status' => 'Completed'
        ],
        [
            'id' => 'A003',
            'patient_id' => 'P003',
            'patient_name' => 'Mike Johnson',
            'doctor_id' => 'D003',
            'doctor_name' => 'Dr. Emily Davis',
            'date' => '2024-03-22',
            'time' => '11:15',
            'department' => 'Orthopedics',
            'status' => 'Cancelled'
        ]
    ];
}

// Get Available Doctors
if (isset($_GET['get_doctors'])) {
    $doctors = isset($_SESSION['doctors']) ? $_SESSION['doctors'] : [];
    echo json_encode($doctors);
    exit;
}

// Get Available Patients
if (isset($_GET['get_patients'])) {
    $patients = isset($_SESSION['patients']) ? $_SESSION['patients'] : [];
    echo json_encode($patients);
    exit;
}
