<?php
session_start();

// Check if user is logged in as patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

// Get patient's medical records
$medical_records = isset($_SESSION['medical_records']) ? array_filter($_SESSION['medical_records'], function($record) {
    return $record['patient_id'] === $_SESSION['user_id'];
}) : [];

// Sort records by date
usort($medical_records, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Hospital Management System</title>
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

        /* Medical Records Container */
        .records-container {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .records-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
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

        /* Records List */
        .records-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .record-card {
            background: var(--white);
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .record-card:hover {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .record-date {
            color: var(--primary-blue);
            font-weight: 500;
        }

        .record-type {
            background: var(--light-blue);
            color: var(--primary-blue);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
        }

        .record-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .detail-label {
            color: #666;
            font-size: 0.875rem;
        }

        .detail-value {
            color: #333;
            font-weight: 500;
        }

        .record-notes {
            background: var(--light-blue);
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }

        .record-notes h4 {
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        .record-notes p {
            color: #666;
            line-height: 1.5;
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
            <a href="appointments.php" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                Appointments
            </a>
            <a href="medical_records.php" class="menu-item active">
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
            <div class="welcome-text">Medical Records</div>
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="records-container">
            <div class="records-header">
                <h2>Medical History</h2>
            </div>

            <div class="search-filter">
                <input type="text" class="search-box" placeholder="Search records..." onkeyup="searchRecords(this.value)">
                <select class="filter-select" onchange="filterByType(this.value)">
                    <option value="">All Types</option>
                    <option value="Consultation">Consultation</option>
                    <option value="Prescription">Prescription</option>
                    <option value="Test Result">Test Result</option>
                    <option value="Procedure">Procedure</option>
                </select>
            </div>

            <div class="records-list">
                <?php if (empty($medical_records)): ?>
                <div class="record-card">
                    <p>No medical records found.</p>
                </div>
                <?php else: ?>
                <?php foreach ($medical_records as $record): ?>
                <div class="record-card">
                    <div class="record-header">
                        <div class="record-date">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('d M Y', strtotime($record['date'])); ?>
                        </div>
                        <span class="record-type"><?php echo htmlspecialchars($record['type']); ?></span>
                    </div>

                    <div class="record-details">
                        <div class="detail-item">
                            <span class="detail-label">Doctor</span>
                            <span class="detail-value">Dr. <?php echo htmlspecialchars($record['doctor_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Department</span>
                            <span class="detail-value"><?php echo htmlspecialchars($record['department']); ?></span>
                        </div>
                        <?php if (isset($record['diagnosis'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Diagnosis</span>
                            <span class="detail-value"><?php echo htmlspecialchars($record['diagnosis']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($record['medication'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Medication</span>
                            <span class="detail-value"><?php echo htmlspecialchars($record['medication']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($record['notes'])): ?>
                    <div class="record-notes">
                        <h4>Notes</h4>
                        <p><?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function searchRecords(query) {
            const cards = document.querySelectorAll('.record-card');
            query = query.toLowerCase();
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? '' : 'none';
            });
        }

        function filterByType(type) {
            const cards = document.querySelectorAll('.record-card');
            
            cards.forEach(card => {
                const typeElement = card.querySelector('.record-type');
                const recordType = typeElement ? typeElement.textContent.trim() : '';
                card.style.display = !type || recordType === type ? '' : 'none';
            });
        }
    </script>
</body>
</html> 