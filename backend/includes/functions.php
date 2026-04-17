<?php
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

// Calculate loyalty discount based on completed trips

function calculateLoyaltyDiscount($trips_completed) {
    if ($trips_completed >= 10) {
        return 0.15;  // 15% off
    } elseif ($trips_completed >= 5) {
        return 0.10;  // 10% off
    } elseif ($trips_completed >= 3) {
        return 0.05;  // 5% off
    }
    return 0.00;
}


// DATE FORMATTING
// Format date for display
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return 'N/A';
    return date($format, strtotime($date));
}


// Generate CSRF token
 
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

//Verify CSRF token
 
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Get pagination data
 
function getPaginationData($current_page, $total_records, $per_page = 10) {
    $total_pages = ceil($total_records / $per_page);
    $offset = ($current_page - 1) * $per_page;
    $prev_page = $current_page > 1 ? $current_page - 1 : 1;
    $next_page = $current_page < $total_pages ? $current_page + 1 : $total_pages;
    
    return [
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => $offset,
        'per_page' => $per_page,
        'prev_page' => $prev_page,
        'next_page' => $next_page,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

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