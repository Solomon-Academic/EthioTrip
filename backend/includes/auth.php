<?php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

//Check if user is logged in

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Require login - redirect if not logged in
 
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

// Require guest - redirect if already logged in
 
function requireGuest() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Require admin - redirect if not admin
 
function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: dashboard.php');
        exit();
    }
}

// Check if current user is admin

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}


// Get current user data

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $conn;
    $user_id = $_SESSION['user_id'];
    $query = "SELECT id, name, email, phone, role, trips_completed, total_spent, loyalty_discount, created_at FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Get user by ID
 
function getUserById($user_id) {
    global $conn;
    $query = "SELECT id, name, email, phone, role, trips_completed, total_spent, loyalty_discount, created_at FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Update user loyalty discount based on completed trips

function updateLoyaltyDiscount($user_id) {
    global $conn;
    
    // Get completed trips count
    $query = "SELECT trips_completed FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if (!$user) {
        return 0;
    }
    
    $trips = (int)$user['trips_completed'];
    
    // Find appropriate discount tier from database
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
    
    // Update discount in users table
    $update = "UPDATE users SET loyalty_discount = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "di", $discount, $user_id);
    mysqli_stmt_execute($stmt);
    
    return $discount;
}

// Update user stats after booking
 
function updateUserStats($user_id, $amount) {
    global $conn;
    
    // Get current stats
    $query = "SELECT trips_completed, total_spent FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if (!$user) {
        return ['trips_completed' => 0, 'total_spent' => 0];
    }
    
    $new_trips = $user['trips_completed'] + 1;
    $new_total_spent = $user['total_spent'] + $amount;
    
    // Update stats
    $update = "UPDATE users SET trips_completed = ?, total_spent = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "idi", $new_trips, $new_total_spent, $user_id);
    mysqli_stmt_execute($stmt);
    
    // Update loyalty discount based on new trip count
    $new_discount = updateLoyaltyDiscount($user_id);
    
    return [
        'trips_completed' => $new_trips,
        'total_spent' => $new_total_spent,
        'loyalty_discount' => $new_discount
    ];
}
?>