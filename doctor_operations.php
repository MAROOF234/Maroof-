<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Initialize doctors array if not exists
if (!isset($_SESSION['doctors'])) {
    $_SESSION['doctors'] = [];
}

// Initialize users array if not exists
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data with default empty values
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';
    $specialization = isset($_POST['specialization']) ? trim($_POST['specialization']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $experience = isset($_POST['experience']) ? trim($_POST['experience']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Available';

    $errors = [];

    // Validate username
    if (empty($username)) {
        $errors[] = 'Username is required';
    }

    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    // Validate password
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }

    // Validate password confirmation
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }

    // Validate department
    if (empty($department)) {
        $errors[] = 'Department is required';
    }

    // Validate specialization
    if (empty($specialization)) {
        $errors[] = 'Specialization is required';
    }

    // Validate phone
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }

    // Validate experience
    if (empty($experience)) {
        $errors[] = 'Experience is required';
    }

    // If no errors, add the doctor
    if (empty($errors)) {
        // Check if email already exists in both doctors and users arrays
        $email_exists = false;
        foreach ($_SESSION['doctors'] as $doctor) {
            if ($doctor['email'] === $email) {
                $email_exists = true;
                break;
            }
        }
        foreach ($_SESSION['users'] as $user) {
            if ($user['email'] === $email) {
                $email_exists = true;
                break;
            }
        }

        if ($email_exists) {
            $errors[] = 'Email already exists';
        } else {
            // Generate unique ID
            $doctor_id = 'D' . str_pad(count($_SESSION['doctors']) + 1, 3, '0', STR_PAD_LEFT);

            // Add new doctor
            $new_doctor = [
                'id' => $doctor_id,
                'username' => $username,
                'email' => $email,
                'password' => $password, // In a real application, this should be hashed
                'department' => $department,
                'specialization' => $specialization,
                'phone' => $phone,
                'experience' => $experience,
                'status' => $status,
                'role' => 'doctor'
            ];

            // Add to doctors array
            $_SESSION['doctors'][] = $new_doctor;

            // Add to users array for login
            $_SESSION['users'][] = $new_doctor;

            $success_message = 'Doctor added successfully!';
        }
    }
}

// Handle Delete Doctor
if (isset($_GET['delete'])) {
    $doctor_id = $_GET['delete'];
    
    // Remove from doctors array
    foreach ($_SESSION['doctors'] as $key => $doctor) {
        if ($doctor['id'] === $doctor_id) {
            unset($_SESSION['doctors'][$key]);
            $_SESSION['doctors'] = array_values($_SESSION['doctors']); // Reindex array
            break;
        }
    }
    
    // Remove from users array
    foreach ($_SESSION['users'] as $key => $user) {
        if ($user['id'] === $doctor_id) {
            unset($_SESSION['users'][$key]);
            $_SESSION['users'] = array_values($_SESSION['users']); // Reindex array
            break;
        }
    }
    
    header('Location: doctor_operations.php?message=Doctor deleted successfully');
    exit;
}

// Get Doctor for Edit
if (isset($_GET['edit'])) {
    $doctor_id = $_GET['edit'];
    foreach ($_SESSION['doctors'] as $doctor) {
        if ($doctor['id'] === $doctor_id) {
            echo json_encode($doctor);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Operations - Hospital Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* ... existing styles ... */
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>HMS Admin</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="doctor_operations.php" class="menu-item active">
                <i class="fas fa-user-md"></i>
                Doctor Operations
            </a>
            <a href="patient_operations.php" class="menu-item">
                <i class="fas fa-procedures"></i>
                Patient Operations
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
            <div class="welcome-text">Doctor Operations</div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="list-style: none;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <div class="form-header">
                <h2>Add New Doctor</h2>
            </div>

            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Full Name</label>
                        <input type="text" name="username" id="username" class="form-control" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" name="department" id="department" class="form-control" value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <input type="text" name="specialization" id="specialization" class="form-control" value="<?php echo isset($_POST['specialization']) ? htmlspecialchars($_POST['specialization']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" name="phone" id="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="experience">Experience (years)</label>
                        <input type="number" name="experience" id="experience" class="form-control" value="<?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="Available" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Available') ? 'selected' : ''; ?>>Available</option>
                            <option value="On Leave" <?php echo (isset($_POST['status']) && $_POST['status'] === 'On Leave') ? 'selected' : ''; ?>>On Leave</option>
                            <option value="Busy" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Busy') ? 'selected' : ''; ?>>Busy</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-user-plus"></i> Add Doctor
                </button>
            </form>
        </div>

        <!-- Display Doctors List -->
        <div class="table-card">
            <div class="table-header">
                <h2>Doctors List</h2>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Specialization</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['doctors'] as $doctor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doctor['id']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['username']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['department']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['status']); ?></td>
                        <td>
                            <a href="?edit=<?php echo $doctor['id']; ?>" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $doctor['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this doctor?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
