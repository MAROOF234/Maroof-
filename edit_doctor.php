<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.html');
    exit();
}

// Get doctor ID from URL
$id = $_GET['id'] ?? '';
if (empty($id)) {
    header('Location: doctors.php');
    exit();
}

// Get doctor data
$doctor = getItem(DOCTORS_FILE, $id);
if (!$doctor) {
    header('Location: doctors.php');
    exit();
}

// Get all departments for the dropdown
$departments = getAllItems(DEPARTMENTS_FILE);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['fullName'] ?? '';
    $specialization = $_POST['specialization'] ?? '';
    $department = $_POST['department'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $qualification = $_POST['qualification'] ?? '';
    $experience = intval($_POST['experience'] ?? 0);
    $consultationFee = floatval($_POST['consultationFee'] ?? 0);
    $schedule = $_POST['schedule'] ?? '';
    
    // Validate required fields
    $errors = [];
    if (empty($fullName)) $errors[] = 'Full name is required';
    if (empty($specialization)) $errors[] = 'Specialization is required';
    if (empty($department)) $errors[] = 'Department is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($qualification)) $errors[] = 'Qualification is required';
    if ($experience <= 0) $errors[] = 'Experience must be greater than 0';
    if ($consultationFee <= 0) $errors[] = 'Consultation fee must be greater than 0';
    if (empty($schedule)) $errors[] = 'Schedule is required';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Validate phone number (10 digits)
    if (!preg_match('/^\d{10}$/', $phone)) {
        $errors[] = 'Phone number must be 10 digits';
    }
    
    // Check if email already exists (excluding current doctor)
    $doctors = getAllItems(DOCTORS_FILE);
    foreach ($doctors as $existingDoctor) {
        if ($existingDoctor['email'] === $email && $existingDoctor['id'] !== $id) {
            $errors[] = 'Email already exists';
            break;
        }
    }
    
    if (empty($errors)) {
        $updatedDoctor = [
            'fullName' => $fullName,
            'specialization' => $specialization,
            'department' => $department,
            'email' => $email,
            'phone' => $phone,
            'qualification' => $qualification,
            'experience' => $experience,
            'consultationFee' => $consultationFee,
            'schedule' => $schedule
        ];
        
        if (updateItem(DOCTORS_FILE, $id, $updatedDoctor)) {
            header('Location: doctors.php');
            exit();
        } else {
            $errors[] = 'Error updating doctor';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor - Hospital Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>HMS Admin</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="active">
                        <a href="doctors.php"><i class="fas fa-user-md"></i> Doctors</a>
                    </li>
                    <li>
                        <a href="patients.php"><i class="fas fa-procedures"></i> Patients</a>
                    </li>
                    <li>
                        <a href="appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a>
                    </li>
                    <li>
                        <a href="departments.php"><i class="fas fa-hospital"></i> Departments</a>
                    </li>
                    <li>
                        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header class="dashboard-header">
                <div class="header-left">
                    <button id="sidebarToggle" class="btn-icon">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2>Edit Doctor</h2>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['user']['fullName']); ?></span>
                        <a href="../logout.php" class="btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="content-header">
                    <h3>Doctor Information</h3>
                    <a href="doctors.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($doctor['fullName']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['name']); ?>" <?php echo ($doctor['department'] === $dept['name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" pattern="\d{10}" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="qualification">Qualification</label>
                        <input type="text" id="qualification" name="qualification" value="<?php echo htmlspecialchars($doctor['qualification']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="experience">Experience (years)</label>
                        <input type="number" id="experience" name="experience" min="0" value="<?php echo htmlspecialchars($doctor['experience']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="consultationFee">Consultation Fee</label>
                        <input type="number" id="consultationFee" name="consultationFee" min="0" step="0.01" value="<?php echo htmlspecialchars($doctor['consultationFee']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="schedule">Schedule</label>
                        <textarea id="schedule" name="schedule" rows="3" required><?php echo htmlspecialchars($doctor['schedule']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Doctor
                        </button>
                        <a href="doctors.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html> 