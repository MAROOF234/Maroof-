<?php
session_start();

// Check if user is logged in as patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

// Get available doctors
$doctors = isset($_SESSION['doctors']) ? $_SESSION['doctors'] : [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $department = $_POST['department'];
    $reason = $_POST['reason'];

    // Get doctor details
    $doctor = array_filter($doctors, function ($doc) use ($doctor_id) {
        return $doc['id'] === $doctor_id;
    });
    $doctor = reset($doctor);

    // Create new appointment
    $appointment = [
        'id' => uniqid(),
        'patient_id' => $_SESSION['user_id'],
        'patient_name' => $_SESSION['username'],
        'doctor_id' => $doctor_id,
        'doctor_name' => $doctor['name'],
        'date' => $date,
        'time' => $time,
        'department' => $department,
        'reason' => $reason,
        'status' => 'Scheduled',
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Add to appointments array
    if (!isset($_SESSION['appointments'])) {
        $_SESSION['appointments'] = [];
    }
    $_SESSION['appointments'][] = $appointment;

    // Redirect to appointments page
    header('Location: appointments.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Hospital Management System</title>
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

        /* Appointment Form */
        .appointment-form {
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
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>HMS Patient</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="appointments.php" class="menu-item active">
                <i class="fas fa-calendar-check"></i>
                Appointments
            </a>
            <a href="medical_records.php" class="menu-item">
                <i class="fas fa-file-medical"></i>
                Medical Records
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
            <div class="welcome-text">Book New Appointment</div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="appointment-form">
            <div class="form-header">
                <h2>Schedule Appointment</h2>
                <p>Please fill in the details below to book your appointment</p>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="department">Department</label>
                    <select name="department" id="department" class="form-control" required onchange="updateDoctors()">
                        <option value="">Select Department</option>
                        <option value="Cardiology">Cardiology</option>
                        <option value="Neurology">Neurology</option>
                        <option value="Orthopedics">Orthopedics</option>
                        <option value="Pediatrics">Pediatrics</option>
                        <option value="Dermatology">Dermatology</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="doctor_id">Doctor</label>
                    <select name="doctor_id" id="doctor_id" class="form-control" required>
                        <option value="">Select Doctor</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo $doctor['id']; ?>" data-department="<?php echo $doctor['specialization']; ?>">
                                Dr. <?php echo htmlspecialchars($doctor['name']); ?> (<?php echo htmlspecialchars($doctor['specialization']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date">Preferred Date</label>
                    <input type="date" name="date" id="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="time">Preferred Time</label>
                    <input type="time" name="time" id="time" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="reason">Reason for Visit</label>
                    <textarea name="reason" id="reason" class="form-control" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-calendar-plus"></i> Book Appointment
                    </button>
                    <a href="appointments.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateDoctors() {
            const department = document.getElementById('department').value;
            const doctorSelect = document.getElementById('doctor_id');
            const options = doctorSelect.getElementsByTagName('option');

            for (let option of options) {
                if (!option.value) continue; // Skip the default option
                const doctorDepartment = option.getAttribute('data-department');
                option.style.display = !department || doctorDepartment === department ? '' : 'none';
            }

            // Reset doctor selection
            doctorSelect.value = '';
        }

        // Set minimum time to current time for today's date
        const dateInput = document.getElementById('date');
        const timeInput = document.getElementById('time');

        dateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();

            if (selectedDate.toDateString() === today.toDateString()) {
                const currentTime = new Date().toTimeString().slice(0, 5);
                timeInput.min = currentTime;
            } else {
                timeInput.min = '09:00';
            }
        });
    </script>
</body>

</html>