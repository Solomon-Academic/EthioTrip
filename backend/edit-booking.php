<?php 
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Edit Booking';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id <= 0) {
    redirectWithMessage('bookings.php', 'Invalid booking ID', 'error');
}

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
    
    $travel_date = trim($_POST['travel_date'] ?? '');
    $travelers = (int)($_POST['travelers'] ?? 1);
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    if (empty($travel_date)) {
        $errors['travel_date'] = 'Travel date is required';
    }

    if (!empty($travel_date) && strtotime($travel_date) < strtotime(date('Y-m-d'))) {
        $errors['travel_date'] = 'Travel date cannot be in the past';
    }
    
    if ($travelers < 1 || $travelers > 20) {
        $errors['travelers'] = 'Number of travelers must be between 1 and 20';
    }

    if (strlen($special_requests) > 500) {
        $errors['special_requests'] = 'Special requests too long (max 500 characters)';
    }
    
    if (empty($errors)) {
        $update = "UPDATE bookings 
                   SET travel_date = ?, number_of_travelers = ?, special_requests = ? 
                   WHERE id = ? AND user_id = ?";
        
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "sisii", $travel_date, $travelers, $special_requests, $booking_id, $_SESSION['user_id']);
        
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
            <?php echo displayError($errors, 'special_requests'); ?>
        </div>
        
        <button type="submit" class="btn-primary">Update Booking</button>
        <a href="bookings.php" class="btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>