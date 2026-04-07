<?php
/**
 * functions.php - Helper functions for EthioTrip
 * Used across all backend PHP files
 */

// ===========================================
// BOOKING FUNCTIONS
// ===========================================

/**
 * Get all bookings for a specific user
 * @param int $user_id - The user ID
 * @return mysqli_result - Result set of bookings
 */
function getUserBookings($user_id) {
    global $conn;
    $query = "SELECT b.*, p.name as package_name 
              FROM bookings b 
              LEFT JOIN packages p ON b.package_id = p.id 
              WHERE b.user_id = ? 
              ORDER BY b.booking_date DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

/**
 * Calculate total cost for a booking
 * @param float $base_price - Package price per person
 * @param int $travelers - Number of travelers
 * @param float $discount_rate - Loyalty discount (0.05 = 5%)
 * @return array - Contains subtotal, discount, tax, grand_total
 */
function calculateBookingTotal($base_price, $travelers, $discount_rate = 0) {
    $subtotal = $base_price * $travelers;
    $discount = $subtotal * $discount_rate;
    $taxable = $subtotal - $discount;
    $tax = $taxable * 0.10;  // 10% VAT
    $grand_total = $taxable + $tax;
    
    return [
        'subtotal' => $subtotal,
        'discount' => $discount,
        'tax' => $tax,
        'grand_total' => $grand_total
    ];
}

/**
 * Redirect with a flash message
 * @param string $url - Destination URL
 * @param string $message - Message to display
 * @param string $type - 'success' or 'error'
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

/**
 * Display flash message (call this in your header)
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'success';
        $message = $_SESSION['flash_message'];
        echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($message) . '</div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// ===========================================
// DATA SANITIZATION (Chapter 3)
// ===========================================

/**
 * Sanitize user input
 * @param string $data - Raw input
 * @return string - Cleaned input
 */
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    if (isset($conn)) {
        $data = mysqli_real_escape_string($conn, $data);
    }
    return $data;
}

/**
 * Validate email format
 * @param string $email - Email to validate
 * @return bool - True if valid
 */
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength
 * @param string $password - Password to validate
 * @return bool - True if meets requirements
 */
function validatePassword($password) {
    return strlen($password) >= 6;
}

/**
 * Display error message for a form field
 * @param array $errors - Array of errors
 * @param string $field - Field name
 * @return string - HTML error span or empty string
 */
function displayError($errors, $field) {
    if (isset($errors[$field])) {
        return '<span class="error-message">' . htmlspecialchars($errors[$field]) . '</span>';
function displayError($errors, $field) {
    if (isset($errors[$field])) {
        return '<span class="error">' . $errors[$field] . '</span>';
    }
    return '';
}

// ===========================================
// LOYALTY DISCOUNT (Chapter 2)
// ===========================================

/**
 * Calculate loyalty discount based on completed trips
 * @param int $trips_completed - Number of trips
 * @return float - Discount rate (0.05, 0.10, 0.15)
 */
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

// ===========================================
// DATE FORMATTING
// ===========================================

/**
 * Format date for display
 * @param string $date - MySQL date
 * @param string $format - PHP date format
 * @return string - Formatted date
 */
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return 'N/A';
    return date($format, strtotime($date));
}

// ===========================================
// CSRF PROTECTION (Security)
// ===========================================

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token - Token from form
 * @return bool - True if valid
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ===========================================
// PAGINATION (Chapter 4 - LIMIT)
// ===========================================

/**
 * Get pagination data
 * @param int $current_page - Current page number
 * @param int $total_records - Total records
 * @param int $per_page - Records per page
 * @return array - Pagination data
 */
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
?>