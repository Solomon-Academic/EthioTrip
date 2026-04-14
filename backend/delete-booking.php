<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = $_GET['id'] ?? 0;

// Verify booking belongs to user and is pending
$check = "SELECT id FROM bookings WHERE id = ? AND user_id = ? AND status = 'pending'";
$stmt = mysqli_prepare($conn, $check);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) == 0) {
    $_SESSION['message'] = "Booking cannot be cancelled";
    $_SESSION['message_type'] = 'error';
    header('Location: bookings.php');
    exit();
}

// Cancel booking
$update = "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $update);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['message'] = "Booking cancelled successfully";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = "Failed to cancel booking";
    $_SESSION['message_type'] = 'error';
}

header('Location: bookings.php');
exit();
?>