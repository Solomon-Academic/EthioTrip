<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Edit Booking';
$booking_id = $_GET['id'] ?? 0;

// Get booking
$query = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    redirectWithMessage('bookings.php', 'Booking not found', 'error');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $travel_date = $_POST['travel_date'] ?? '';
    $travelers = $_POST['travelers'] ?? 1;
    $special_requests = $_POST['special_requests'] ?? '';
    
    if (empty($travel_date)) {
        $errors['travel_date'] = 'Travel date is required';
    }
    
    if ($travelers < 1 || $travelers > 20) {
        $errors['travelers'] = 'Number of travelers must be between 1 and 20';
    }
    
    if (empty($errors)) {
        $update = "UPDATE bookings SET travel_date = ?, number_of_travelers = ?, special_requests = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "sisi", $travel_date, $travelers, $special_requests, $booking_id);
        
        if (mysqli_stmt_execute($stmt)) {
            redirectWithMessage('bookings.php', 'Booking updated successfully!', 'success');
        } else {
            $errors['general'] = 'Failed to update booking';
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h1>Edit Booking #<?php echo $booking['id']; ?></h1>
    
    <?php if (isset($errors['general'])): ?>
        <div class="alert alert-error"><?php echo $errors['general']; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Package</label>
            <input type="text" value="<?php echo htmlspecialchars($booking['package_name']); ?>" disabled>
        </div>
        
        <div class="form-group">
            <label>Travel Date *</label>
            <input type="date" name="travel_date" 
                   value="<?php echo $_POST['travel_date'] ?? $booking['travel_date']; ?>" required>
            <?php echo displayError($errors, 'travel_date'); ?>
        </div>
        
        <div class="form-group">
            <label>Number of Travelers *</label>
            <input type="number" name="travelers" min="1" max="20" 
                   value="<?php echo $_POST['travelers'] ?? $booking['number_of_travelers']; ?>" required>
            <?php echo displayError($errors, 'travelers'); ?>
        </div>
        
        <div class="form-group">
            <label>Special Requests</label>
            <textarea name="special_requests" rows="3"><?php echo $_POST['special_requests'] ?? $booking['special_requests']; ?></textarea>
        </div>
        
        <button type="submit" class="btn-primary">Update Booking</button>
        <a href="bookings.php" class="btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
