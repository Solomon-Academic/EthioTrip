<?php
session_start();
require_once '../config/database.php';

// Simple admin check
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $check = mysqli_query($conn, "SELECT role FROM users WHERE id = " . $_SESSION['user_id']);
    $user = mysqli_fetch_assoc($check);
    $is_admin = ($user && $user['role'] === 'admin');
}

if (!$is_admin) {
    header('Location: ../login.php');
    exit();
}

$adjustment = floatval($_POST['adjustment'] ?? 0);

if ($adjustment != 0) {
    // Update all active tiers
    $query = "UPDATE discount_tiers SET discount_percent = discount_percent + ? WHERE is_active = 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "d", $adjustment);
    mysqli_stmt_execute($stmt);
    
    // Ensure no negative discounts
    mysqli_query($conn, "UPDATE discount_tiers SET discount_percent = 0 WHERE discount_percent < 0");
    
    // Recalculate all user discounts
    $users = mysqli_query($conn, "SELECT id, trips_completed FROM users");
    while ($user = mysqli_fetch_assoc($users)) {
        $trips = $user['trips_completed'];
        
        $tier_query = "SELECT discount_percent FROM discount_tiers 
                       WHERE is_active = 1 
                       AND min_trips <= ? 
                       AND (max_trips IS NULL OR max_trips >= ?)
                       ORDER BY min_trips DESC LIMIT 1";
        $stmt = mysqli_prepare($conn, $tier_query);
        mysqli_stmt_bind_param($stmt, "ii", $trips, $trips);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $tier = mysqli_fetch_assoc($result);
        
        $discount = $tier ? floatval($tier['discount_percent']) / 100 : 0;
        
        $update = "UPDATE users SET loyalty_discount = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "di", $discount, $user['id']);
        mysqli_stmt_execute($stmt);
    }
    
    $_SESSION['message'] = "Discounts adjusted by " . ($adjustment > 0 ? '+' : '') . $adjustment . "%";
}

header('Location: discounts.php');
exit();
?>