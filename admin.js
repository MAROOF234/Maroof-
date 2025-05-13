// Check if user is authenticated
function checkAuth() {
    const currentUser = JSON.parse(localStorage.getItem('currentUser'));
    if (!currentUser || currentUser.role !== 'admin') {
        window.location.href = '../index.html';
    }
    return currentUser;
}

// Initialize dashboard
function initDashboard() {
    const currentUser = checkAuth();
    
    // Update user info
    const userNameElement = document.querySelector('.user-info span');
    if (userNameElement) {
        userNameElement.textContent = `Welcome, ${currentUser.fullName}`;
    }
    
    // Update statistics
    updateStats();
    
    // Initialize charts
    initCharts();
    
    // Setup sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
}

// Update dashboard statistics
function updateStats() {
    // Get data from localStorage
    const doctors = JSON.parse(localStorage.getItem('doctors')) || [];
    const patients = JSON.parse(localStorage.getItem('users')) || [];
    const appointments = JSON.parse(localStorage.getItem('appointments')) || [];
    const departments = JSON.parse(localStorage.getItem('departments')) || [];
    
    // Update counters
    document.getElementById('totalDoctors').textContent = doctors.length;
    document.getElementById('totalPatients').textContent = patients.filter(u => u.role === 'patient').length;
    document.getElementById('totalAppointments').textContent = appointments.length;
    document.getElementById('totalDepartments').textContent = departments.length;
}

// Initialize charts
function initCharts() {
    // Get appointments data
    const appointments = JSON.parse(localStorage.getItem('appointments')) || [];
    const departments = JSON.parse(localStorage.getItem('departments')) || [];
    
    // Prepare appointments chart data
    const appointmentsByMonth = {};
    appointments.forEach(appointment => {
        const month = new Date(appointment.date).toLocaleString('default', { month: 'long' });
        appointmentsByMonth[month] = (appointmentsByMonth[month] || 0) + 1;
    });
    
    // Create appointments chart
    const appointmentsCtx = document.getElementById('appointmentsChart').getContext('2d');
    new Chart(appointmentsCtx, {
        type: 'line',
        data: {
            labels: Object.keys(appointmentsByMonth),
            datasets: [{
                label: 'Appointments',
                data: Object.values(appointmentsByMonth),
                borderColor: '#1a237e',
                backgroundColor: 'rgba(26, 35, 126, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
    
    // Prepare departments chart data
    const doctorsByDepartment = {};
    departments.forEach(dept => {
        const doctors = JSON.parse(localStorage.getItem('doctors')) || [];
        doctorsByDepartment[dept.name] = doctors.filter(d => d.department === dept.name).length;
    });
    
    // Create departments chart
    const departmentsCtx = document.getElementById('departmentsChart').getContext('2d');
    new Chart(departmentsCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(doctorsByDepartment),
            datasets: [{
                data: Object.values(doctorsByDepartment),
                backgroundColor: [
                    '#1a237e',
                    '#283593',
                    '#303f9f',
                    '#3949ab',
                    '#3f51b5'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
}

// Toggle sidebar
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('collapsed');
    
    // Adjust main content margin
    const mainContent = document.querySelector('.main-content');
    mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? '70px' : '250px';
}

// Handle logout
function handleLogout() {
    localStorage.removeItem('currentUser');
    window.location.href = '../index.html';
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', initDashboard); 