<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

$name = isset($_GET['name']) ? trim($_GET['name']) : '';

// If user is logged in, use their session name
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT id, name, trips_completed, total_spent, loyalty_discount FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
        $trips = (int)$user['trips_completed'];
        $discount_decimal = floatval($user['loyalty_discount']);
        $discount_percent = $discount_decimal * 100;
        
        // Get tier name
        $tier_query = "SELECT tier_name FROM discount_tiers 
                       WHERE is_active = 1 
                       AND min_trips <= ? 
                       AND (max_trips IS NULL OR max_trips >= ?)
                       ORDER BY min_trips DESC LIMIT 1";
        $stmt = mysqli_prepare($conn, $tier_query);
        mysqli_stmt_bind_param($stmt, "ii", $trips, $trips);
        mysqli_stmt_execute($stmt);
        $tier_result = mysqli_stmt_get_result($stmt);
        $tier = mysqli_fetch_assoc($tier_result);
        $tier_name = $tier ? $tier['tier_name'] : 'Bronze';
        
        echo json_encode([
            'success' => true,
            'user_id' => $user['id'],
            'name' => $user['name'],
            'trips_completed' => $trips,
            'total_spent' => floatval($user['total_spent']),
            'discount_percent' => $discount_percent,
            'discount_decimal' => $discount_decimal,
            'tier_name' => $tier_name
        ]);
        exit();
    }
}

// If not logged in or user not found, search by name
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name required', 'discount_decimal' => 0]);
    exit();
}

$query = "SELECT id, name, trips_completed, total_spent, loyalty_discount FROM users WHERE LOWER(name) = LOWER(?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo json_encode([
        'success' => false, 
        'message' => 'New user - no trips completed yet',
        'discount_decimal' => 0,
        'discount_percent' => 0,
        'trips_completed' => 0,
        'total_spent' => 0,
        'tier_name' => 'Bronze'
    ]);
    exit();
}

$trips = (int)$user['trips_completed'];
$discount_decimal = floatval($user['loyalty_discount']);
$discount_percent = $discount_decimal * 100;

$tier_query = "SELECT tier_name FROM discount_tiers 
               WHERE is_active = 1 
               AND min_trips <= ? 
               AND (max_trips IS NULL OR max_trips >= ?)
               ORDER BY min_trips DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $tier_query);
mysqli_stmt_bind_param($stmt, "ii", $trips, $trips);
mysqli_stmt_execute($stmt);
$tier_result = mysqli_stmt_get_result($stmt);
$tier = mysqli_fetch_assoc($tier_result);
$tier_name = $tier ? $tier['tier_name'] : 'Bronze';

echo json_encode([
    'success' => true,
    'user_id' => $user['id'],
    'name' => $user['name'],
    'trips_completed' => $trips,
    'total_spent' => floatval($user['total_spent']),
    'discount_percent' => $discount_percent,
    'discount_decimal' => $discount_decimal,
    'tier_name' => $tier_name
]);

mysqli_close($conn);
?>