<?php
// This file only contains functions NOT in auth.php

function calculateBookingTotal($price, $travelers, $discount_rate = 0) {
    $subtotal = $price * $travelers;
    $discount = $subtotal * $discount_rate;
    $total = $subtotal - $discount;
    $tax = $total * 0.10;
    $grand_total = $total + $tax;
    
    return [
        'subtotal' => $subtotal,
        'discount' => $discount,
        'total' => $total,
        'tax' => $tax,
        'grand_total' => $grand_total
    ];
}

function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Location: ' . $url);
    exit();
}

function getUserBookings($user_id, $limit = null) {
    global $conn;
    $query = "SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC";
    if ($limit) $query .= " LIMIT " . $limit;
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function getDiscountTier($trips) {
    global $conn;
    $query = "SELECT tier_name, discount_percent FROM discount_tiers 
              WHERE is_active = 1 
              AND min_trips <= ? 
              AND (max_trips IS NULL OR max_trips >= ?)
              ORDER BY min_trips DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $trips, $trips);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function getNextTier($current_trips) {
    global $conn;
    $query = "SELECT min_trips, tier_name, discount_percent FROM discount_tiers 
              WHERE is_active = 1 AND min_trips > ? 
              ORDER BY min_trips ASC LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $current_trips);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $next = mysqli_fetch_assoc($result);
    
    if ($next) {
        return [
            'trips_needed' => $next['min_trips'] - $current_trips,
            'tier_name' => $next['tier_name'],
            'discount_percent' => $next['discount_percent']
        ];
    }
    return null;
}
?>