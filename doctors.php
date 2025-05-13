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

require_once '../includes/DataHandler.php';
$dataHandler = new DataHandler();
$doctors = $dataHandler->getAllDoctors();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $doctorData = [
                    'id' => $dataHandler->generateId('D'),
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'role' => 'doctor',
                    'department' => $_POST['department'],
                    'specialization' => $_POST['specialization'],
                    'phone' => $_POST['phone'],
                    'gender' => $_POST['gender'],
                    'age' => $_POST['age'],
                    'address' => $_POST['address'],
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Add to users.json
                $dataHandler->addUser([
                    'id' => $doctorData['id'],
                    'username' => $doctorData['username'],
                    'email' => $doctorData['email'],
                    'password' => $doctorData['password'],
                    'role' => 'doctor'
                ]);

                // Add to doctors.json
                $dataHandler->addDoctor($doctorData);
                header('Location: doctors.php');
                exit;
                break;

            case 'delete':
                if (isset($_POST['doctor_id'])) {
                    $dataHandler->deleteDoctor($_POST['doctor_id']);
                    header('Location: doctors.php');
                    exit;
                }
                break;
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

    header('Location: doctors.php?message=Doctor deleted successfully');
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
    <title>Doctors Management - Hospital Management System</title>
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

        /* Doctors Table */
        .doctors-container {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .doctors-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .add-doctor-btn {
            background: var(--primary-blue);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .add-doctor-btn:hover {
            background: var(--dark-blue);
        }

        .doctors-table {
            width: 100%;
            border-collapse: collapse;
        }

        .doctors-table th,
        .doctors-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .doctors-table th {
            background: var(--light-blue);
            color: var(--primary-blue);
            font-weight: 500;
        }

        .doctors-table tr:hover {
            background: var(--light-blue);
        }

        .action-btn {
            padding: 0.5rem;
            border-radius: 5px;
            color: var(--white);
            text-decoration: none;
            margin-right: 0.5rem;
            transition: all 0.3s;
        }

        .edit-btn {
            background: var(--primary-blue);
        }

        .edit-btn:hover {
            background: var(--dark-blue);
        }

        .delete-btn {
            background: #dc3545;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        /* Search and Filter */
        .search-filter {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-box {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .filter-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            min-width: 150px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            overflow: auto;
            background: rgba(0, 0, 0, 0.3);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #fff;
            margin: 5% auto;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
            max-width: 500px;
            width: 95%;
            padding: 2rem 2rem 1.5rem 2rem;
            position: relative;
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
            cursor: pointer;
        }

        .close-btn:hover {
            color: #c62828;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-blue);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .submit-btn {
            background: var(--primary-blue);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            background: var(--dark-blue);
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
        }

        .status-leave {
            background: #fff3cd;
            color: #856404;
        }
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
            <a href="patients.php" class="menu-item">
                <i class="fas fa-user-injured"></i>
                Patients
            </a>
            <a href="doctors.php" class="menu-item active">
                <i class="fas fa-user-md"></i>
                Doctors
            </a>
            <a href="appointments.php" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                Appointments
            </a>
            <a href="settings.php" class="menu-item">
                <i class="fas fa-cog"></i>
                Settings
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="welcome-text">Doctors Management</div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="message">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="message">
                <ul style="list-style: none;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="doctors-container">
            <div class="doctors-header">
                <h2>Doctor Records</h2>
                <button onclick="openAddModal()" class="add-doctor-btn">
                    <i class="fas fa-plus"></i> Add New Doctor
                </button>
            </div>

            <div class="search-filter">
                <input type="text" class="search-box" placeholder="Search doctors..." onkeyup="searchDoctors(this.value)">
                <select class="filter-select" onchange="filterBySpecialization(this.value)">
                    <option value="">All Specializations</option>
                    <option value="Cardiology">Cardiology</option>
                    <option value="Neurology">Neurology</option>
                    <option value="Orthopedics">Orthopedics</option>
                </select>
            </div>

            <table class="doctors-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>Experience</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($doctors as $doctor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doctor['id']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['username']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                            <td><?php echo isset($doctor['experience']) ? htmlspecialchars($doctor['experience']) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                            <td>
                                <span class="status-badge <?php echo (isset($doctor['status']) ? $doctor['status'] : 'Available') === 'Available' ? 'status-available' : 'status-leave'; ?>">
                                    <?php echo htmlspecialchars(isset($doctor['status']) ? $doctor['status'] : 'Available'); ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="openEditModal('<?php echo $doctor['id']; ?>')" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                    <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this doctor?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Doctor Modal -->
    <div id="addDoctorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Doctor</h2>
                <span class="close-btn" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="username">Full Name</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <option value="Cardiology">Cardiology</option>
                        <option value="Neurology">Neurology</option>
                        <option value="Pediatrics">Pediatrics</option>
                        <option value="Orthopedics">Orthopedics</option>
                        <option value="Dermatology">Dermatology</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="specialization">Specialization</label>
                    <input type="text" id="specialization" name="specialization" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" min="25" max="70" required>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" required rows="3"></textarea>
                </div>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-user-md"></i>
                    Add Doctor
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Doctor Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
            <h2>Edit Doctor</h2>
            <form action="doctor_operations.php" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="doctor_id" id="edit_doctor_id">
                <div class="form-group">
                    <label for="edit_username">Name</label>
                    <input type="text" id="edit_username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="edit_specialization">Specialization</label>
                    <input type="text" id="edit_specialization" name="specialization" required>
                </div>
                <div class="form-group">
                    <label for="edit_experience">Experience</label>
                    <input type="text" id="edit_experience" name="experience" required>
                </div>
                <div class="form-group">
                    <label for="edit_phone">Contact</label>
                    <input type="tel" id="edit_phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="Available">Available</option>
                        <option value="On Leave">On Leave</option>
                    </select>
                </div>
                <button type="submit" class="submit-btn">Update Doctor</button>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addDoctorModal').style.display = 'flex';
        }

        function openEditModal(doctorId) {
            fetch(`doctor_operations.php?edit=${doctorId}`)
                .then(response => response.json())
                .then(doctor => {
                    document.getElementById('edit_doctor_id').value = doctor.id;
                    document.getElementById('edit_username').value = doctor.username;
                    document.getElementById('edit_specialization').value = doctor.specialization;
                    document.getElementById('edit_experience').value = doctor.experience;
                    document.getElementById('edit_phone').value = doctor.phone;
                    document.getElementById('edit_email').value = doctor.email;
                    document.getElementById('edit_status').value = doctor.status;
                    document.getElementById('editModal').style.display = 'block';
                });
        }

        function closeModal(modalId) {
            if (modalId) {
                document.getElementById(modalId).style.display = 'none';
            } else {
                document.getElementById('addDoctorModal').style.display = 'none';
            }
        }

        function searchDoctors(query) {
            const rows = document.querySelectorAll('.doctors-table tbody tr');
            query = query.toLowerCase();

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }

        function filterBySpecialization(specialization) {
            const rows = document.querySelectorAll('.doctors-table tbody tr');

            rows.forEach(row => {
                const spec = row.cells[2].textContent;
                row.style.display = !specialization || spec === specialization ? '' : 'none';
            });
        }

        // Close modal when clicking outside modal-content
        window.onclick = function(event) {
            var addModal = document.getElementById('addDoctorModal');
            if (event.target === addModal) {
                addModal.style.display = 'none';
            }
            var editModal = document.getElementById('editModal');
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
        }
    </script>
</body>

</html>