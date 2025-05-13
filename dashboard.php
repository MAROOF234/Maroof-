<?php
session_start();

// Check if user is logged in as patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

// Get patient's appointments
$appointments = isset($_SESSION['appointments']) ? array_filter($_SESSION['appointments'], function ($apt) {
    return $apt['patient_id'] === $_SESSION['user_id'];
}) : [];

// Get upcoming appointments
$upcoming_appointments = array_filter($appointments, function ($apt) {
    return strtotime($apt['date']) >= time() && $apt['status'] === 'Scheduled';
});

// Get medical records
$medical_records = isset($_SESSION['medical_records'][$_SESSION['user_id']]) ?
    $_SESSION['medical_records'][$_SESSION['user_id']] : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Hospital Management System</title>
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

        /* Dashboard Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card h2 {
            color: var(--primary-blue);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-blue);
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: var(--light-blue);
            border-radius: 5px;
        }

        .stat-value {
            font-size: 1.5rem;
            color: var(--primary-blue);
            font-weight: bold;
        }

        .stat-label {
            color: var(--dark-blue);
            font-size: 0.875rem;
        }

        /* Recent Activity */
        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid var(--light-blue);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: var(--light-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-blue);
        }

        .activity-details {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            color: var(--dark-blue);
        }

        .activity-time {
            font-size: 0.875rem;
            color: #666;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .action-btn {
            background: var(--primary-blue);
            color: var(--white);
            padding: 1rem;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: var(--dark-blue);
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
            <a href="dashboard.php" class="menu-item active">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="appointments.php" class="menu-item">
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
            <div class="welcome-text">Patient Dashboard</div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="dashboard-grid">
            <!-- Appointments Overview -->
            <div class="dashboard-card">
                <h2>Appointments Overview</h2>
                <div class="stat-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo count($upcoming_appointments); ?></div>
                        <div class="stat-label">Upcoming</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo count($appointments); ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                </div>
                <div class="quick-actions">
                    <a href="book_appointment.php" class="action-btn">
                        <i class="fas fa-plus"></i> Book Appointment
                    </a>
                </div>
            </div>

            <!-- Medical Records Summary -->
            <div class="dashboard-card">
                <h2>Medical Records</h2>
                <div class="stat-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo count($medical_records); ?></div>
                        <div class="stat-label">Total Records</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo isset($medical_records['last_visit']) ? date('d M Y', strtotime($medical_records['last_visit'])) : 'N/A'; ?></div>
                        <div class="stat-label">Last Visit</div>
                    </div>
                </div>
                <div class="quick-actions">
                    <a href="medical_records.php" class="action-btn">
                        <i class="fas fa-file-medical"></i> View Records
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-card">
            <h2>Recent Activity</h2>
            <ul class="activity-list">
                <?php
                $activities = [];
                foreach ($appointments as $apt) {
                    $activities[] = [
                        'type' => 'appointment',
                        'title' => "Appointment with Dr. {$apt['doctor_name']}",
                        'time' => strtotime("{$apt['date']} {$apt['time']}"),
                        'icon' => 'calendar-check'
                    ];
                }
                foreach ($medical_records as $record) {
                    $activities[] = [
                        'type' => 'record',
                        'title' => "Medical Record Updated",
                        'time' => strtotime($record['date']),
                        'icon' => 'file-medical'
                    ];
                }
                usort($activities, function ($a, $b) {
                    return $b['time'] - $a['time'];
                });
                $activities = array_slice($activities, 0, 5);
                foreach ($activities as $activity):
                ?>
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-<?php echo $activity['icon']; ?>"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></div>
                            <div class="activity-time"><?php echo date('M d, Y H:i', $activity['time']); ?></div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>

</html>