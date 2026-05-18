// Form validation functions

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePassword(password) {
    return password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password);
}

function validateName(name) {
    return name.trim().length >= 2;
}

function showError(elementId, message) {
    const errorElement = document.getElementById(elementId + '-error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

function hideError(elementId) {
    const errorElement = document.getElementById(elementId + '-error');
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    }
}

// Login form validation
function validateLoginForm() {
    const email = document.getElementById('email')?.value;
    const password = document.getElementById('password')?.value;
    let isValid = true;

    if (!email || !validateEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    } else {
        hideError('email');
    }

    if (!password || password.length < 8) {
        showError('password', 'Password must be at least 8 characters with uppercase and number');
        isValid = false;
    } else if (!/[A-Z]/.test(password)) {
        showError('password', 'Password must contain an uppercase letter');
        isValid = false;
    } else if (!/[0-9]/.test(password)) {
        showError('password', 'Password must contain a number');
        isValid = false;
    } else {
        hideError('password');
    }

    return isValid;
}

// Registration form validation
function validateRegistrationForm() {
    const name = document.getElementById('name')?.value;
    const email = document.getElementById('email')?.value;
    const phone = document.getElementById('phone')?.value;
    const password = document.getElementById('password')?.value;
    const confirmPassword = document.getElementById('confirm_password')?.value;
    let isValid = true;
    
    if (!name || name.trim().length < 2) {
        showError('name', 'Full name is required (minimum 2 characters)');
        isValid = false;
    } else {
        hideError('name');
    }
    
    if (!email || !validateEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    } else {
        hideError('email');
    }
    
    if (!phone || phone.trim().length < 10) {
        showError('phone', 'Valid phone number is required');
        isValid = false;
    } else {
        hideError('phone');
    }
    
    if (!password || password.length < 8) {
        showError('password', 'Password must be at least 8 characters');
        isValid = false;
    } else if (!/[A-Z]/.test(password)) {
        showError('password', 'Password must contain an uppercase letter');
        isValid = false;
    } else if (!/[0-9]/.test(password)) {
        showError('password', 'Password must contain a number');
        isValid = false;
    } else {
        hideError('password');
    }

    if (password !== confirmPassword) {
        showError('confirm_password', 'Passwords do not match');
        isValid = false;
    } else {
        hideError('confirm_password');
    }

    return isValid;
}

// Show toast notification
function showToast(message, type = 'success') {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            background: ${type === 'error' ? '#e74c3c' : '#27ae60'};
            color: white;
            border-radius: 8px;
            z-index: 9999;
            display: none;
            animation: slideIn 0.3s ease;
        `;
        document.body.appendChild(toast);
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Confirm delete action
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

// Format currency
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Session management functions
async function isUserLoggedIn() {
    try {
        const response = await fetch('../backend/check-login.php');
        const data = await response.json();
        return data.logged_in;
    } catch(e) {
        return false;
    }
}

async function getCurrentUser() {
    try {
        const response = await fetch('../backend/check-login.php');
        const data = await response.json();
        return data.logged_in ? data : null;
    } catch(e) {
        return null;
    }
}

function requireLogin(redirectUrl = '../backend/login.php') {
    isUserLoggedIn().then(loggedIn => {
        if (!loggedIn) {
            localStorage.setItem('returnAfterLogin', window.location.href);
            window.location.href = redirectUrl;
        }
    });
}

// Auto-update navigation based on login status
async function updateNavForSession() {
    const user = await getCurrentUser();
    const navLinks = document.querySelector('.nav-links');
    
    if (!navLinks) return;
    
    // Remove existing dynamic menu items
    document.querySelectorAll('.dynamic-nav-item').forEach(el => el.remove());
    
    if (user) {
        // Logged in view
        const li = document.createElement('li');
        li.className = 'dynamic-nav-item';
        li.innerHTML = `
            <a href="../backend/dashboard.php" style="color: #d4af37;">
                <i class="fas fa-user-circle"></i> ${user.user_name}
            </a>
            <a href="../backend/bookings.php" style="margin-left: 15px;">
                <i class="fas fa-calendar"></i> My Bookings
            </a>
            <a href="../backend/logout.php" style="margin-left: 15px;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        `;
        navLinks.appendChild(li);
    } else {
        // Guest view
        const li = document.createElement('li');
        li.className = 'dynamic-nav-item';
        li.innerHTML = `
            <a href="../backend/login.php"><i class="fas fa-sign-in-alt"></i> Sign In</a>
            <a href="../backend/registration.php" style="margin-left: 15px;"><i class="fas fa-user-plus"></i> Register</a>
        `;
        navLinks.appendChild(li);
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', updateNavForSession);