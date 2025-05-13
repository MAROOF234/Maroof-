<?php
session_start();

// Check if user is logged in as doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

// Get all patients
$patients = isset($_SESSION['patients']) ? $_SESSION['patients'] : [];

// Get doctor's appointments to filter patients
$appointments = isset($_SESSION['appointments']) ? array_filter($_SESSION['appointments'], function ($apt) {
    return $apt['doctor_id'] === $_SESSION['user_id'];
}) : [];

// Get unique patient IDs from appointments
$patient_ids = array_unique(array_column($appointments, 'patient_id'));

// Filter patients who have appointments with this doctor
$my_patients = array_filter($patients, function ($patient) use ($patient_ids) {
    return in_array($patient['id'], $patient_ids);
});
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - Hospital Management System</title>
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

        /* Patients Table */
        .patients-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .patients-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .search-box {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
        }

        .patients-table {
            width: 100%;
            border-collapse: collapse;
        }

        .patients-table th,
        .patients-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .patients-table th {
            background: var(--light-blue);
            color: var(--primary-blue);
            font-weight: 500;
        }

        .action-btn {
            background: var(--primary-blue);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: var(--dark-blue);
        }

        .action-btn.secondary {
            background: #f5f5f5;
            color: #333;
        }

        .action-btn.secondary:hover {
            background: #e0e0e0;
        }

        /* Patient Info Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            color: var(--primary-blue);
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .patient-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .info-group {
            margin-bottom: 1rem;
        }

        .info-label {
            font-weight: 500;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: #333;
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
            <a href="view_patients.php" class="menu-item active">
                <i class="fas fa-users"></i>
                Patients
            </a>
            <a href="addrecord.php" class="menu-item">
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
            <div class="welcome-text">My Patients</div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="patients-card">
            <div class="patients-header">
                <div class="search-box">
                    <input type="text" class="search-input" placeholder="Search patients...">
                </div>
            </div>

            <table class="patients-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Last Visit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_patients as $patient): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($patient['username']); ?></td>
                            <td><?php echo htmlspecialchars($patient['email']); ?></td>
                            <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                            <td>
                                <?php
                                $last_visit = null;
                                foreach ($appointments as $apt) {
                                    if ($apt['patient_id'] === $patient['id'] && $apt['status'] === 'Completed') {
                                        if (!$last_visit || strtotime($apt['date']) > strtotime($last_visit)) {
                                            $last_visit = $apt['date'];
                                        }
                                    }
                                }
                                echo $last_visit ? date('M d, Y', strtotime($last_visit)) : 'No visits yet';
                                ?>
                            </td>
                            <td>
                                <a href="#" class="action-btn" onclick="showPatientInfo('<?php echo $patient['id']; ?>')">
                                    <i class="fas fa-info-circle"></i> View Details
                                </a>
                                <a href="addrecord.php?patient_id=<?php echo $patient['id']; ?>" class="action-btn secondary">
                                    <i class="fas fa-file-medical"></i> Add Record
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Patient Info Modal -->
    <div id="patientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Patient Information</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="patient-info">
                <div class="info-group">
                    <div class="info-label">Name</div>
                    <div class="info-value" id="modalName"></div>
                </div>
                <div class="info-group">
                    <div class="info-label">Email</div>
                    <div class="info-value" id="modalEmail"></div>
                </div>
                <div class="info-group">
                    <div class="info-label">Phone</div>
                    <div class="info-value" id="modalPhone"></div>
                </div>
                <div class="info-group">
                    <div class="info-label">Address</div>
                    <div class="info-value" id="modalAddress"></div>
                </div>
                <div class="info-group">
                    <div class="info-label">Blood Group</div>
                    <div class="info-value" id="modalBloodGroup"></div>
                </div>
                <div class="info-group">
                    <div class="info-label">Allergies</div>
                    <div class="info-value" id="modalAllergies"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.querySelector('.search-input').addEventListener('input', function(e) {
            const searchText = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.patients-table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Modal functionality
        function showPatientInfo(patientId) {
            const patient = <?php echo json_encode($my_patients); ?>.find(p => p.id === patientId);
            if (patient) {
                document.getElementById('modalName').textContent = patient.username;
                document.getElementById('modalEmail').textContent = patient.email;
                document.getElementById('modalPhone').textContent = patient.phone;
                document.getElementById('modalAddress').textContent = patient.address;
                document.getElementById('modalBloodGroup').textContent = patient.blood_group;
                document.getElementById('modalAllergies').textContent = patient.allergies || 'None';

                document.getElementById('patientModal').style.display = 'block';
            }
        }

        function closeModal() {
            document.getElementById('patientModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('patientModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>