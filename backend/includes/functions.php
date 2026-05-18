<?php

// Sanitize user input to prevent XSS and SQL injection
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Safe output escaping for HTML display (prevents XSS)
function safe($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Validate email format
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate password strength (minimum 8 characters, must have uppercase and number)
function validatePassword($password) {
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

// Validate Ethiopian phone number (09XXXXXXXX format)
function validatePhone($phone) {
    return preg_match('/^09[0-9]{8}$/', $phone);
}

// Display error message for form field
function displayError($errors, $field) {
    if (isset($errors[$field])) {
        return '<span class="error">' . htmlspecialchars($errors[$field]) . '</span>';
    }
    return '';
}

// Display flash message from session
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'success';
        $message = $_SESSION['flash_message'];
        echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($message) . '</div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// Get discount tier based on completed trips from database
function getDiscountTierFromDB($trips_completed) {
    global $conn;
    $query = "SELECT tier_name, discount_percent FROM discount_tiers 
              WHERE is_active = 1 
              AND min_trips <= ? 
              AND (max_trips IS NULL OR max_trips >= ?)
              ORDER BY min_trips DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $trips_completed, $trips_completed);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Calculate loyalty discount from database
function calculateLoyaltyDiscountFromDB($trips_completed) {
    $tier = getDiscountTierFromDB($trips_completed);
    return $tier ? floatval($tier['discount_percent']) / 100 : 0;
}

// DEPRECATED: Old hardcoded version - kept for backward compatibility
function calculateLoyaltyDiscount($trips_completed) {
    if ($trips_completed >= 10) {
        return 0.15;
    } elseif ($trips_completed >= 5) {
        return 0.10;
    } elseif ($trips_completed >= 3) {
        return 0.05;
    }
    return 0.00;
}

// Get discount tier information
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

// Get next tier information for progress tracking
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

// Format date for display
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return 'N/A';
    return date($format, strtotime($date));
}

// Format datetime for display
function formatDateTime($datetime) {
    if (empty($datetime)) return 'N/A';
    return date('M d, Y g:i A', strtotime($datetime));
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Get CSRF token field HTML
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// Rate limiting for login attempts (prevent brute force)
function checkLoginAttempts($email) {
    $lockKey = 'login_attempt_' . hash('sha256', $email);
    $attempts = $_SESSION[$lockKey]['count'] ?? 0;
    $lastAttempt = $_SESSION[$lockKey]['time'] ?? 0;
    $limitWindow = 900; // 15 minutes
    $maxAttempts = 5;

    // Reset if window has passed
    if (time() - $lastAttempt > $limitWindow) {
        return ['allowed' => true];
    }

    if ($attempts >= $maxAttempts) {
        $remaining = $limitWindow - (time() - $lastAttempt);
        return [
            'allowed' => false,
            'message' => 'Too many login attempts. Please try again in ' . ceil($remaining / 60) . ' minutes.'
        ];
    }

    return ['allowed' => true];
}

// Record failed login attempt
function recordFailedLogin($email) {
    $lockKey = 'login_attempt_' . hash('sha256', $email);

    if (!isset($_SESSION[$lockKey])) {
        $_SESSION[$lockKey] = ['count' => 0, 'time' => time()];
    }

    $_SESSION[$lockKey]['count']++;
    $_SESSION[$lockKey]['time'] = time();
}

// Clear login attempts on successful login
function clearLoginAttempts($email) {
    $lockKey = 'login_attempt_' . hash('sha256', $email);
    unset($_SESSION[$lockKey]);
}

// Get pagination data
function getPaginationData($current_page, $total_records, $per_page = 10) {
    $total_pages = ceil($total_records / $per_page);
    $offset = ($current_page - 1) * $per_page;
    $prev_page = max(1, $current_page - 1);
    $next_page = min($total_pages, $current_page + 1);
    
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

// Generate pagination HTML
function generatePagination($current_page, $total_pages, $url = '?') {
    if ($total_pages <= 1) return '';
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $html .= '<a href="' . $url . 'page=' . ($current_page - 1) . '" class="page-link">&laquo; Previous</a>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="page-current">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $url . 'page=' . $i . '" class="page-link">' . $i . '</a>';
        }
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $url . 'page=' . ($current_page + 1) . '" class="page-link">Next &raquo;</a>';
    }
    
    $html .= '</div>';
    return $html;
}


// Calculate booking total with discount and tax
function calculateBookingTotal($price, $travelers, $discount_rate = 0) {
    $subtotal = $price * $travelers;
    $discount = $subtotal * $discount_rate;
    $total = $subtotal - $discount;
    $tax = $total * 0.10;  // 10% VAT
    $grand_total = $total + $tax;
    
    return [
        'subtotal' => round($subtotal, 2),
        'discount' => round($discount, 2),
        'total' => round($total, 2),
        'tax' => round($tax, 2),
        'grand_total' => round($grand_total, 2)
    ];
}


// Redirect with flash message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Location: ' . $url);
    exit();
}


// Get user bookings
function getUserBookings($user_id, $limit = null) {
    global $conn;
    $query = "SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC";
    if ($limit) $query .= " LIMIT " . intval($limit);
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// Get all active discount tiers
function getAllDiscountTiers() {
    global $conn;
    return mysqli_query($conn, "SELECT * FROM discount_tiers WHERE is_active = 1 ORDER BY min_trips ASC");
}

// Get popular destinations (most booked)
function getPopularDestinations($limit = 5) {
    global $conn;
    $query = "SELECT destination, COUNT(*) as booking_count 
              FROM bookings 
              WHERE destination IS NOT NULL AND destination != ''
              GROUP BY destination 
              ORDER BY booking_count DESC 
              LIMIT ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $destinations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $destinations[] = $row;
    }
    return $destinations;
}


// Format currency (USD)
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Format Ethiopian Birr
function formatBirr($amount) {
    return 'ETB ' . number_format($amount, 2);
}

// Truncate text to specified length
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . $suffix;
}

// Generate random transaction ID
function generateTransactionId() {
    return 'ET-' . strtoupper(uniqid()) . '-' . rand(1000, 9999);
}
?>