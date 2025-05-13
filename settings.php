<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                // Update admin profile
                $_SESSION['username'] = $_POST['username'];
                $_SESSION['email'] = $_POST['email'];
                $message = "Profile updated successfully";
                break;

            case 'change_password':
                // Change password
                if ($_POST['new_password'] === $_POST['confirm_password']) {
                    $_SESSION['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    $message = "Password changed successfully";
                } else {
                    $error = "Passwords do not match";
                }
                break;

            case 'update_preferences':
                // Update system preferences
                $_SESSION['system_preferences'] = [
                    'hospital_name' => $_POST['hospital_name'],
                    'timezone' => $_POST['timezone'],
                    'date_format' => $_POST['date_format'],
                    'time_format' => $_POST['time_format'],
                    'theme' => $_POST['theme']
                ];
                $message = "System preferences updated successfully";
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Hospital Management System</title>
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

        /* Settings Container */
        .settings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .settings-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .settings-card h2 {
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-blue);
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
            width: 100%;
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

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .settings-section {
            margin-bottom: 2rem;
        }

        .settings-section h3 {
            color: var(--dark-blue);
            margin-bottom: 1rem;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--primary-blue);
        }

        input:checked+.slider:before {
            transform: translateX(26px);
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
            <a href="doctors.php" class="menu-item">
                <i class="fas fa-user-md"></i>
                Doctors
            </a>
            <a href="appointments.php" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                Appointments
            </a>
            <a href="settings.php" class="menu-item active">
                <i class="fas fa-cog"></i>
                Settings
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="welcome-text">System Settings</div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <?php if (isset($message)): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="message error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="settings-container">
            <!-- Profile Settings -->
            <div class="settings-card">
                <h2>Profile Settings</h2>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                    </div>
                    <button type="submit" class="submit-btn">Update Profile</button>
                </form>
            </div>

            <!-- Password Settings -->
            <div class="settings-card">
                <h2>Change Password</h2>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="submit-btn">Change Password</button>
                </form>
            </div>

            <!-- System Preferences -->
            <div class="settings-card">
                <h2>System Preferences</h2>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="update_preferences">
                    <div class="form-group">
                        <label for="hospital_name">Hospital Name</label>
                        <input type="text" id="hospital_name" name="hospital_name" value="<?php echo htmlspecialchars($_SESSION['system_preferences']['hospital_name'] ?? 'Hospital Management System'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select id="timezone" name="timezone" required>
                            <option value="UTC" <?php echo ($_SESSION['system_preferences']['timezone'] ?? '') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                            <option value="EST" <?php echo ($_SESSION['system_preferences']['timezone'] ?? '') === 'EST' ? 'selected' : ''; ?>>EST</option>
                            <option value="PST" <?php echo ($_SESSION['system_preferences']['timezone'] ?? '') === 'PST' ? 'selected' : ''; ?>>PST</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_format">Date Format</label>
                        <select id="date_format" name="date_format" required>
                            <option value="Y-m-d" <?php echo ($_SESSION['system_preferences']['date_format'] ?? '') === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                            <option value="d-m-Y" <?php echo ($_SESSION['system_preferences']['date_format'] ?? '') === 'd-m-Y' ? 'selected' : ''; ?>>DD-MM-YYYY</option>
                            <option value="m-d-Y" <?php echo ($_SESSION['system_preferences']['date_format'] ?? '') === 'm-d-Y' ? 'selected' : ''; ?>>MM-DD-YYYY</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="time_format">Time Format</label>
                        <select id="time_format" name="time_format" required>
                            <option value="24" <?php echo ($_SESSION['system_preferences']['time_format'] ?? '') === '24' ? 'selected' : ''; ?>>24-hour</option>
                            <option value="12" <?php echo ($_SESSION['system_preferences']['time_format'] ?? '') === '12' ? 'selected' : ''; ?>>12-hour</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="theme">Theme</label>
                        <select id="theme" name="theme" required>
                            <option value="light" <?php echo ($_SESSION['system_preferences']['theme'] ?? '') === 'light' ? 'selected' : ''; ?>>Light</option>
                            <option value="dark" <?php echo ($_SESSION['system_preferences']['theme'] ?? '') === 'dark' ? 'selected' : ''; ?>>Dark</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Update Preferences</button>
                </form>
            </div>

            <!-- Notification Settings -->
            <div class="settings-card">
                <h2>Notification Settings</h2>
                <div class="settings-section">
                    <h3>Email Notifications</h3>
                    <div class="form-group">
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                        <span>Appointment Reminders</span>
                    </div>
                    <div class="form-group">
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                        <span>System Updates</span>
                    </div>
                </div>
                <div class="settings-section">
                    <h3>System Notifications</h3>
                    <div class="form-group">
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                        <span>New Appointments</span>
                    </div>
                    <div class="form-group">
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                        <span>Patient Updates</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add any JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Handle toggle switches
            const toggles = document.querySelectorAll('.toggle-switch input');
            toggles.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    // Here you can add AJAX calls to save the notification preferences
                    console.log('Toggle changed:', this.checked);
                });
            });
        });
    </script>
</body>

</html>