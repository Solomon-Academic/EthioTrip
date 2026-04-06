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