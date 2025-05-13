<?php
session_start();
require_once '../includes/DataHandler.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$dataHandler = new DataHandler();

// Define departments array
$departments = [
    ['name' => 'Cardiology'],
    ['name' => 'Neurology'],
    ['name' => 'Orthopedics'],
    ['name' => 'Pediatrics'],
    ['name' => 'Dermatology'],
    ['name' => 'Gynecology'],
    ['name' => 'Ophthalmology'],
    ['name' => 'ENT'],
    ['name' => 'General Medicine']
];

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
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = $_POST['address'] ?? '';

    // Validate required fields
    $errors = [];
    if (empty($fullName)) $errors[] = 'Full name is required';
    if (empty($specialization)) $errors[] = 'Specialization is required';
    if (empty($department)) $errors[] = 'Department is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($qualification)) $errors[] = 'Qualification is required';
    if (empty($gender)) $errors[] = 'Gender is required';
    if (empty($address)) $errors[] = 'Address is required';
    if ($experience <= 0) $errors[] = 'Experience must be greater than 0';
    if ($consultationFee <= 0) $errors[] = 'Consultation fee must be greater than 0';
    if (empty($schedule)) $errors[] = 'Schedule is required';
    if (empty($password)) $errors[] = 'Password is required';
    if (empty($confirm_password)) $errors[] = 'Confirm password is required';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    // Validate phone number (10 digits)
    if (!preg_match('/^\d{10}$/', $phone)) {
        $errors[] = 'Phone number must be 10 digits';
    }

    // Check if email already exists
    $existingDoctors = $dataHandler->getAllDoctors();
    foreach ($existingDoctors as $doctor) {
        if ($doctor['email'] === $email) {
            $errors[] = 'Email already exists';
            break;
        }
    }

    if (empty($errors)) {
        $doctorData = [
            'id' => $dataHandler->generateId('D'),
            'fullName' => $fullName,
            'specialization' => $specialization,
            'department' => $department,
            'email' => $email,
            'phone' => $phone,
            'gender' => $gender,
            'qualification' => $qualification,
            'experience' => $experience,
            'consultationFee' => $consultationFee,
            'schedule' => $schedule,
            'address' => $address,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Add to users.json
        $userData = [
            'id' => $doctorData['id'],
            'username' => $email,
            'email' => $email,
            'password' => $doctorData['password'],
            'role' => 'doctor'
        ];

        if ($dataHandler->addUser($userData) && $dataHandler->addDoctor($doctorData)) {
            header('Location: doctors.php?success=Doctor added successfully');
            exit();
        } else {
            $errors[] = 'Error adding doctor';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Doctor - Hospital Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: rgba(0, 0, 0, 0.2);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
            max-width: 500px;
            width: 95%;
            padding: 2rem 2rem 1.5rem 2rem;
            position: relative;
            margin: 2rem 0;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close-btn {
            position: absolute;
            top: 18px;
            right: 22px;
            font-size: 1.5rem;
            color: #888;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.2s;
        }

        .close-btn:hover {
            color: #c62828;
        }

        .modal h2 {
            color: #1a73e8;
            margin-bottom: 1.5rem;
            font-size: 1.6rem;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            color: #1a73e8;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #1a73e8;
            outline: none;
            box-shadow: 0 0 5px rgba(26, 115, 232, 0.15);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary {
            background: #1a73e8;
            color: #fff;
        }

        .btn-primary:hover {
            background: #0d47a1;
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.2rem;
        }

        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .alert ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .alert li {
            margin-bottom: 0.5rem;
        }

        .alert li:last-child {
            margin-bottom: 0;
        }

        @media (max-width: 600px) {
            .modal {
                padding: 1rem 0.5rem 1rem 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="modal">
        <a href="doctors.php" class="close-btn" title="Close">&times;</a>
        <h2>Add New Doctor</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" id="fullName" name="fullName" value="<?php echo isset($_POST['fullName']) ? htmlspecialchars($_POST['fullName']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <select id="department" name="department" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept['name']); ?>" <?php echo (isset($_POST['department']) && $_POST['department'] === $dept['name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="specialization">Specialization</label>
                <input type="text" id="specialization" name="specialization" value="<?php echo isset($_POST['specialization']) ? htmlspecialchars($_POST['specialization']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="qualification">Qualification</label>
                <input type="text" id="qualification" name="qualification" value="<?php echo isset($_POST['qualification']) ? htmlspecialchars($_POST['qualification']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="experience">Experience (years)</label>
                <input type="number" id="experience" name="experience" min="0" value="<?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="consultationFee">Consultation Fee</label>
                <input type="number" id="consultationFee" name="consultationFee" min="0" step="0.01" value="<?php echo isset($_POST['consultationFee']) ? htmlspecialchars($_POST['consultationFee']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="schedule">Schedule</label>
                <textarea id="schedule" name="schedule" rows="3" required><?php echo isset($_POST['schedule']) ? htmlspecialchars($_POST['schedule']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Doctor</button>
                <a href="doctors.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>

</html>