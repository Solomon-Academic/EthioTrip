// Form validation functions

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePassword(password) {
    return password.length >= 6;
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
    
    if (!password || password.length < 6) {
        showError('password', 'Password must be at least 6 characters');
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
    
    if (!password || password.length < 6) {
        showError('password', 'Password must be at least 6 characters');
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
        
        // Add animation
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