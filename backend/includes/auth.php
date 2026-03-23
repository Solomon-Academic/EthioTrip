<?php
require_once 'config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if logged in
function requireGuest() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Get current user data
function getCurrentUser() {
    if (isLoggedIn()) {
        global $conn;
        $user_id = $_SESSION['user_id'];
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    return null;
}

// Update user loyalty discount based on trips
function updateLoyaltyDiscount($user_id) {
    global $conn;
    $query = "SELECT trips_completed FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    $trips = $user['trips_completed'];
    $discount = 0;
    
    if ($trips >= 10) {
        $discount = 0.15;
    } elseif ($trips >= 5) {
        $discount = 0.10;
    } elseif ($trips >= 3) {
        $discount = 0.05;
    }
    
    $update = "UPDATE users SET loyalty_discount = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "di", $discount, $user_id);
    mysqli_stmt_execute($stmt);
    
    return $discount;
}

// Sanitize input
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate password strength
function validatePassword($password) {
    return strlen($password) >= 6;
}

// Display error messages
function displayError($errors, $field) {
    if (isset($errors[$field])) {
        return '<span class="error">' . $errors[$field] . '</span>';
    }
    return '';
}
?>