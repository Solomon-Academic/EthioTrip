<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$booking_id = $_GET['id'] ?? 0;

// Verify booking belongs to user and is pending
$check = "SELECT id FROM bookings WHERE id = ? AND user_id = ? AND status = 'pending'";
$stmt = mysqli_prepare($conn, $check);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) == 0) {
    redirectWithMessage('bookings.php', 'Booking cannot be cancelled', 'error');
}

// Cancel booking
$update = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
$stmt = mysqli_prepare($conn, $update);
mysqli_stmt_bind_param($stmt, "i", $booking_id);

if (mysqli_stmt_execute($stmt)) {
    redirectWithMessage('bookings.php', 'Booking cancelled successfully', 'success');
} else {
    redirectWithMessage('bookings.php', 'Failed to cancel booking', 'error');
}
?>