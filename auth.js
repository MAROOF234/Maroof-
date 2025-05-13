// Check if user is already logged in
document.addEventListener('DOMContentLoaded', function() {
    const currentUser = JSON.parse(localStorage.getItem('currentUser'));
    if (currentUser) {
        window.location.href = currentUser.role === 'admin' ? 'admin/dashboard.php' : 'patient/dashboard.php';
    }
});

// Handle login form submission
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(loginForm);
        
        try {
            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Store user data in localStorage for client-side access
                localStorage.setItem('currentUser', JSON.stringify(data.user));
                
                // Redirect to appropriate dashboard
                window.location.href = data.redirect;
            } else {
                showAlert(data.message, 'danger');
            }
        } catch (error) {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

// Handle registration form submission
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(registerForm);
        
        try {
            const response = await fetch('register.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('Registration successful! Redirecting to login...', 'success');
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 2000);
            } else {
                showAlert(data.message, 'danger');
            }
        } catch (error) {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

// Helper function to show alerts
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const form = document.querySelector('form');
    form.insertBefore(alertDiv, form.firstChild);
} 