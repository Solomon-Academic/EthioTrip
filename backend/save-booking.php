<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Log for debugging
$log = fopen("booking_log.txt", "a");
fwrite($log, date('Y-m-d H:i:s') . " - Received: " . print_r($data, true) . "\n");

$package_name = $data['package_name'] ?? '';
$package_price = floatval($data['package_price'] ?? 0);
$travel_date = $data['travel_date'] ?? date('Y-m-d', strtotime('+30 days'));
$number_of_travelers = intval($data['travelers'] ?? 1);
$payment_method = $data['payment_method'] ?? 'credit_card';
$transaction_id = $data['transaction_id'] ?? 'TXN-' . time();
$final_amount = floatval($data['final_amount'] ?? $package_price);
$user_name = trim($data['user_name'] ?? '');

fwrite($log, "User name from payment: $user_name\n");
fwrite($log, "Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n");
fwrite($log, "Session user_name: " . ($_SESSION['user_name'] ?? 'NOT SET') . "\n");

// Validate
if (empty($package_name)) {
    fwrite($log, "ERROR: Package name required\n");
    fclose($log);
    echo json_encode(['success' => false, 'message' => 'Package name is required']);
    exit();
}

// PRIORITY: Use logged-in user ID if available
$user_id = null;
$trips_before = 0;
$total_spent_before = 0;

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // User is logged in - use their ID
    $user_id = $_SESSION['user_id'];
    fwrite($log, "Using logged-in user ID: $user_id\n");
    
    // Get user details from database
    $find_user = "SELECT id, name, trips_completed, total_spent FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $find_user);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
        $trips_before = intval($user['trips_completed']);
        $total_spent_before = floatval($user['total_spent']);
        $user_name = $user['name'];
        fwrite($log, "Logged-in user found: Name=$user_name, Trips=$trips_before\n");
    } else {
        fwrite($log, "ERROR: Logged-in user not found in database\n");
        fclose($log);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
} else {
    // Not logged in - find or create user by name
    fwrite($log, "User not logged in, using name: $user_name\n");
    
    if (empty($user_name)) {
        fwrite($log, "ERROR: User name required for non-logged-in user\n");
        fclose($log);
        echo json_encode(['success' => false, 'message' => 'User name is required']);
        exit();
    }
    
    $find_user = "SELECT id, name, trips_completed, total_spent FROM users WHERE name = ?";
    $stmt = mysqli_prepare($conn, $find_user);
    mysqli_stmt_bind_param($stmt, "s", $user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
        $user_id = $user['id'];
        $trips_before = intval($user['trips_completed']);
        $total_spent_before = floatval($user['total_spent']);
        fwrite($log, "Existing user found: ID=$user_id, Trips=$trips_before\n");
    } else {
        // Create new user
        $temp_email = strtolower(str_replace(' ', '', $user_name)) . '@ethiotrip.com';
        $temp_password = password_hash('changeme123', PASSWORD_DEFAULT);
        $temp_phone = '0000000000';
        
        $insert_user = "INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'user')";
        $stmt = mysqli_prepare($conn, $insert_user);
        mysqli_stmt_bind_param($stmt, "ssss", $user_name, $temp_email, $temp_password, $temp_phone);
        
        if (mysqli_stmt_execute($stmt)) {
            $user_id = mysqli_insert_id($conn);
            $trips_before = 0;
            $total_spent_before = 0;
            fwrite($log, "New user created: ID=$user_id, Name=$user_name\n");
        } else {
            fwrite($log, "ERROR: Failed to create user: " . mysqli_error($conn) . "\n");
            fclose($log);
            echo json_encode(['success' => false, 'message' => 'Failed to create user']);
            exit();
        }
    }
}

// Get discount based on trips
$discount_rate = 0;
$discount_percent = 0;
$tier_query = "SELECT discount_percent, tier_name FROM discount_tiers 
               WHERE is_active = 1 
               AND min_trips <= ? 
               AND (max_trips IS NULL OR max_trips >= ?)
               ORDER BY min_trips DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $tier_query);
mysqli_stmt_bind_param($stmt, "ii", $trips_before, $trips_before);
mysqli_stmt_execute($stmt);
$tier_result = mysqli_stmt_get_result($stmt);
$tier = mysqli_fetch_assoc($tier_result);
$discount_rate = $tier ? floatval($tier['discount_percent']) / 100 : 0;
$discount_percent = $discount_rate * 100;
$tier_name = $tier ? $tier['tier_name'] : 'Bronze';

// Calculate amounts
$subtotal = $package_price * $number_of_travelers;
$discount_amount = $subtotal * $discount_rate;
$total_after_discount = $subtotal - $discount_amount;
$tax = $total_after_discount * 0.10;
$grand_total = $total_after_discount + $tax;

fwrite($log, "Calculation: Subtotal=$subtotal, Discount=$discount_amount, Tax=$tax, Total=$grand_total\n");

// Insert booking
$query = "INSERT INTO bookings (user_id, package_name, travel_date, number_of_travelers, 
          total_amount, discount_amount, final_amount, payment_method, transaction_id, 
          payment_status, status, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', 'confirmed', NOW())";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "issidddss", 
    $user_id, $package_name, $travel_date, $number_of_travelers,
    $subtotal, $discount_amount, $grand_total, $payment_method, $transaction_id
);

if (mysqli_stmt_execute($stmt)) {
    $booking_id = mysqli_insert_id($conn);
    fwrite($log, "Booking inserted: ID=$booking_id\n");
    
    // Update user stats
    $new_trips = $trips_before + 1;
    $new_total_spent = $total_spent_before + $grand_total;
    
    $update_user = "UPDATE users SET total_spent = ?, trips_completed = ? WHERE id = ?";
    $stmt2 = mysqli_prepare($conn, $update_user);
    mysqli_stmt_bind_param($stmt2, "dii", $new_total_spent, $new_trips, $user_id);
    mysqli_stmt_execute($stmt2);
    fwrite($log, "User updated: New trips=$new_trips, New total spent=$new_total_spent\n");
    
    // Get new discount based on updated trips
    $new_tier_query = "SELECT discount_percent, tier_name FROM discount_tiers 
                       WHERE is_active = 1 
                       AND min_trips <= ? 
                       AND (max_trips IS NULL OR max_trips >= ?)
                       ORDER BY min_trips DESC LIMIT 1";
    $stmt3 = mysqli_prepare($conn, $new_tier_query);
    mysqli_stmt_bind_param($stmt3, "ii", $new_trips, $new_trips);
    mysqli_stmt_execute($stmt3);
    $new_tier_result = mysqli_stmt_get_result($stmt3);
    $new_tier = mysqli_fetch_assoc($new_tier_result);
    $new_discount = $new_tier ? floatval($new_tier['discount_percent']) / 100 : 0;
    $new_tier_name = $new_tier ? $new_tier['tier_name'] : 'Bronze';
    
    // Update loyalty discount in users table
    $update_discount = "UPDATE users SET loyalty_discount = ? WHERE id = ?";
    $stmt4 = mysqli_prepare($conn, $update_discount);
    mysqli_stmt_bind_param($stmt4, "di", $new_discount, $user_id);
    mysqli_stmt_execute($stmt4);
    
    fclose($log);
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking saved successfully!',
        'booking_id' => $booking_id,
        'trips_completed' => $new_trips,
        'total_spent' => $new_total_spent,
        'final_amount' => $grand_total,
        'discount_applied' => $discount_percent,
        'tier_name' => $tier_name,
        'new_tier_name' => $new_tier_name,
        'new_discount' => $new_discount * 100
    ]);
} else {
    fwrite($log, "ERROR: Insert failed: " . mysqli_error($conn) . "\n");
    fclose($log);
    echo json_encode(['success' => false, 'message' => 'Failed to save booking: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>