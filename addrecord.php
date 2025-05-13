<?php
session_start();

// Check if user is logged in as doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

// Get patient ID from URL if provided
$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : null;

// Get patient information if ID is provided
$patient = null;
if ($patient_id && isset($_SESSION['patients'])) {
    foreach ($_SESSION['patients'] as $p) {
        if ($p['id'] === $patient_id) {
            $patient = $p;
            break;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $record = [
        'id' => uniqid(),
        'patient_id' => $_POST['patient_id'],
        'doctor_id' => $_SESSION['user_id'],
        'date' => date('Y-m-d'),
        'type' => $_POST['type'],
        'diagnosis' => $_POST['diagnosis'],
        'medication' => $_POST['medication'],
        'notes' => $_POST['notes']
    ];

    // Add record to session
    if (!isset($_SESSION['medical_records'])) {
        $_SESSION['medical_records'] = [];
    }
    $_SESSION['medical_records'][] = $record;

    // Redirect to patient's medical records
    header('Location: view_patients.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medical Record - Hospital Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-blue: #1a73e8;
            --dark-blue: #0d47a1;
            --light-blue: #e8f0fe;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: var(--light-blue);
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 250px;
            background: var(--primary-blue);
            color: var(--white);
            padding: 1rem;
            transition: all 0.3s;
        }

        .sidebar-header {
            padding: 1rem 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .sidebar-menu {
            margin-top: 2rem;
        }

        .menu-item {
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--white);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }

        .header {
            background: var(--white);
            padding: 1rem 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text {
            font-size: 1.5rem;
            color: var(--primary-blue);
        }

        .logout-btn {
            background: var(--primary-blue);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: var(--dark-blue);
        }

        /* Form Styles */
        .form-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            margin-bottom: 2rem;
        }

        .form-header h2 {
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            outline: none;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            background: var(--primary-blue);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            background: var(--dark-blue);
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>HMS Doctor</h2>
            <p>Welcome, Dr. <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="appointments.php" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                Appointments
            </a>
            <a href="view_patients.php" class="menu-item">
                <i class="fas fa-users"></i>
                Patients
            </a>
            <a href="addrecord.php" class="menu-item active">
                <i class="fas fa-file-medical"></i>
                Add Record
            </a>
            <a href="profile.php" class="menu-item">
                <i class="fas fa-user"></i>
                Profile
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="welcome-text">Add Medical Record</div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="form-card">
            <div class="form-header">
                <h2>New Medical Record</h2>
                <?php if ($patient): ?>
                    <p>Adding record for <?php echo htmlspecialchars($patient['username']); ?></p>
                <?php endif; ?>
            </div>

            <form method="POST" action="">
                <?php if ($patient): ?>
                    <input type="hidden" name="patient_id" value="<?php echo $patient['id']; ?>">
                <?php else: ?>
                    <div class="form-group">
                        <label for="patient_id">Select Patient</label>
                        <select name="patient_id" id="patient_id" class="form-control" required>
                            <option value="">Select a patient...</option>
                            <?php
                            $my_patients = array_filter($_SESSION['patients'], function ($p) {
                                return in_array($p['id'], array_unique(array_column(
                                    array_filter($_SESSION['appointments'], function ($apt) {
                                        return $apt['doctor_id'] === $_SESSION['user_id'];
                                    }),
                                    'patient_id'
                                )));
                            });
                            foreach ($my_patients as $p):
                            ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo htmlspecialchars($p['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="type">Record Type</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="">Select type...</option>
                        <option value="Consultation">Consultation</option>
                        <option value="Prescription">Prescription</option>
                        <option value="Test Result">Test Result</option>
                        <option value="Procedure">Procedure</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="diagnosis">Diagnosis</label>
                    <textarea name="diagnosis" id="diagnosis" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label for="medication">Medication</label>
                    <textarea name="medication" id="medication" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea name="notes" id="notes" class="form-control"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Save Record
                    </button>
                    <a href="view_patients.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>