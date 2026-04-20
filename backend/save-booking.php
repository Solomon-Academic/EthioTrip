<?php
require_once 'config/database.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);

$data = json_decode(file_get_contents('php://input'), true);

$package_name = $data['package_name'] ?? '';
$package_price = floatval($data['package_price'] ?? 0);
$destination = $data['destination'] ?? '';
$start_date = $data['start_date'] ?? '';
$end_date = $data['end_date'] ?? '';
$number_of_travelers = intval($data['travelers'] ?? 1);
$payment_method = $data['payment_method'] ?? 'credit_card';
$transaction_id = $data['transaction_id'] ?? 'TXN-' . time();
$final_amount = floatval($data['final_amount'] ?? $package_price);
$user_name = trim($data['user_name'] ?? '');

// Calculate duration
$duration_days = 0;
if (!empty($start_date) && !empty($end_date)) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $duration_days = $interval->days;
}

if (empty($package_name)) {
    echo json_encode(['success' => false, 'message' => 'Package name is required']);
    exit();
}

if (empty($start_date) || empty($end_date)) {
    echo json_encode(['success' => false, 'message' => 'Travel dates are required']);
    exit();
}

$user_id = null;
$trips_before = 0;
$total_spent_before = 0;

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
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
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
} else {
    if (empty($user_name)) {
        echo json_encode(['success' => false, 'message' => 'User name is required']);
        exit();
    }
    
    $find_user = "SELECT id, name, trips_completed, total_spent FROM users WHERE LOWER(name) = LOWER(?)";
    $stmt = mysqli_prepare($conn, $find_user);
    mysqli_stmt_bind_param($stmt, "s", $user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
        $user_id = $user['id'];
        $trips_before = intval($user['trips_completed']);
        $total_spent_before = floatval($user['total_spent']);
    } else {
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
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create user']);
            exit();
        }
    }
}

// Get discount based on trips
$discount_rate = 0;
$tier_query = "SELECT discount_percent FROM discount_tiers 
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

// Calculate amounts (daily rate based on package price / 3 days standard)
$daily_rate = $package_price / 3;
$subtotal = $daily_rate * $duration_days * $number_of_travelers;
$discount_amount = $subtotal * $discount_rate;
$total_after_discount = $subtotal - $discount_amount;
$tax = $total_after_discount * 0.10;
$grand_total = $total_after_discount + $tax;

// Insert booking with date range
$query = "INSERT INTO bookings (user_id, package_name, destination, start_date, end_date, duration_days, 
          number_of_travelers, total_amount, discount_amount, final_amount, payment_method, transaction_id, 
          payment_status, status, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', 'confirmed', NOW())";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "issssiidddss", 
    $user_id, $package_name, $destination, $start_date, $end_date, $duration_days,
    $number_of_travelers, $subtotal, $discount_amount, $grand_total, $payment_method, $transaction_id
);

if (mysqli_stmt_execute($stmt)) {
    $booking_id = mysqli_insert_id($conn);
    
    // Update user stats
    $new_trips = $trips_before + 1;
    $new_total_spent = $total_spent_before + $grand_total;
    
    $update_user = "UPDATE users SET total_spent = ?, trips_completed = ? WHERE id = ?";
    $stmt2 = mysqli_prepare($conn, $update_user);
    mysqli_stmt_bind_param($stmt2, "dii", $new_total_spent, $new_trips, $user_id);
    mysqli_stmt_execute($stmt2);
    
    // Update or insert user_destination record
    if (!empty($destination)) {
        $check_dest = "SELECT id, visit_count FROM user_destinations WHERE user_id = ? AND destination = ?";
        $stmt3 = mysqli_prepare($conn, $check_dest);
        mysqli_stmt_bind_param($stmt3, "is", $user_id, $destination);
        mysqli_stmt_execute($stmt3);
        $dest_result = mysqli_stmt_get_result($stmt3);
        $existing_dest = mysqli_fetch_assoc($dest_result);
        
        if ($existing_dest) {
            $new_count = $existing_dest['visit_count'] + 1;
            $update_dest = "UPDATE user_destinations SET visit_count = ?, last_visited = ? WHERE id = ?";
            $stmt4 = mysqli_prepare($conn, $update_dest);
            mysqli_stmt_bind_param($stmt4, "isi", $new_count, $start_date, $existing_dest['id']);
            mysqli_stmt_execute($stmt4);
        } else {
            $insert_dest = "INSERT INTO user_destinations (user_id, destination, visit_count, last_visited) VALUES (?, ?, 1, ?)";
            $stmt4 = mysqli_prepare($conn, $insert_dest);
            mysqli_stmt_bind_param($stmt4, "iss", $user_id, $destination, $start_date);
            mysqli_stmt_execute($stmt4);
        }
    }
    
    // Get new discount
    $new_tier_query = "SELECT discount_percent FROM discount_tiers 
                       WHERE is_active = 1 
                       AND min_trips <= ? 
                       AND (max_trips IS NULL OR max_trips >= ?)
                       ORDER BY min_trips DESC LIMIT 1";
    $stmt5 = mysqli_prepare($conn, $new_tier_query);
    mysqli_stmt_bind_param($stmt5, "ii", $new_trips, $new_trips);
    mysqli_stmt_execute($stmt5);
    $new_tier_result = mysqli_stmt_get_result($stmt5);
    $new_tier = mysqli_fetch_assoc($new_tier_result);
    $new_discount = $new_tier ? floatval($new_tier['discount_percent']) / 100 : 0;
    
    $update_discount = "UPDATE users SET loyalty_discount = ? WHERE id = ?";
    $stmt6 = mysqli_prepare($conn, $update_discount);
    mysqli_stmt_bind_param($stmt6, "di", $new_discount, $user_id);
    mysqli_stmt_execute($stmt6);
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking saved successfully!',
        'booking_id' => $booking_id,
        'trips_completed' => $new_trips,
        'total_spent' => $new_total_spent,
        'final_amount' => $grand_total,
        'destination' => $destination,
        'duration_days' => $duration_days,
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save booking: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>