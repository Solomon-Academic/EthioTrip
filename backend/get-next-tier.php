<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

$trips = isset($_GET['trips']) ? intval($_GET['trips']) : 0;

$query = "SELECT min_trips, tier_name, discount_percent FROM discount_tiers 
          WHERE is_active = 1 AND min_trips > ? 
          ORDER BY min_trips ASC LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $trips);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$next = mysqli_fetch_assoc($result);

if ($next) {
    echo json_encode([
        'success' => true,
        'trips_needed' => $next['min_trips'] - $trips,
        'tier_name' => $next['tier_name'],
        'discount_percent' => $next['discount_percent']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Maximum tier reached'
    ]);
}

mysqli_close($conn);
?>