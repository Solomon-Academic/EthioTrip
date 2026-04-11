<?php
/**
 * auth.php - Authentication functions for EthioTrip
 * Handles login, session management, and user data
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';  // ADDED: Include functions

// ===========================================
// SESSION MANAGEMENT
// ===========================================

/**
 * Check if user is logged in
 * @return bool - True if logged in
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

/**
 * Require guest - redirect if already logged in
 */
function requireGuest() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit();
    }
}

// ===========================================
// USER DATA FUNCTIONS
// ===========================================

/**
 * Get current user data
 * @return array|null - User data or null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
function getCurrentUser() {
    if (isLoggedIn()) {
        global $conn;
        $user_id = $_SESSION['user_id'];
        $query = "SELECT id, name, email, phone, role, trips_completed, total_spent, loyalty_discount, created_at FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    global $conn;
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

/**
 * Update user loyalty discount based on completed trips
 * @param int $user_id - User ID
 * @return float - New discount rate
 */
function updateLoyaltyDiscount($user_id) {
    global $conn;
    
    // Get completed trips count
    $query = "SELECT trips_completed FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    $trips = $user['trips_completed'] ?? 0;
    $discount = calculateLoyaltyDiscount($trips);
    $trips = $user['trips_completed'];
    
    $tier_query = "SELECT discount_percent FROM discount_tiers 
                   WHERE is_active = 1 
                   AND min_trips <= ? 
                   AND (max_trips IS NULL OR max_trips >= ?)
                   ORDER BY min_trips DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $tier_query);
    mysqli_stmt_bind_param($stmt, "ii", $trips, $trips);
    mysqli_stmt_execute($stmt);
    $tier_result = mysqli_stmt_get_result($stmt);
    $tier = mysqli_fetch_assoc($tier_result);
    $discount = $tier ? floatval($tier['discount_percent']) / 100 : 0;
    
    // Update discount
    $update = "UPDATE users SET loyalty_discount = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "di", $discount, $user_id);
    mysqli_stmt_execute($stmt);
    return $discount;
}

// Only ONE sanitize function
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return strlen($password) >= 6;
}

function displayError($errors, $field) {
    if (isset($errors[$field])) {
        return '<span class="error">' . $errors[$field] . '</span>';
    }
    return '';
}
?>