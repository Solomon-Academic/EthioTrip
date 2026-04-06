<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Create Booking';
$user = getCurrentUser();
$packages_query = "SELECT * FROM packages WHERE is_active = 1";
$packages = mysqli_query($conn, $packages_query);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_id = $_POST['package_id'] ?? '';
    $travel_date = $_POST['travel_date'] ?? '';
    $travelers = $_POST['travelers'] ?? 1;
    $special_requests = $_POST['special_requests'] ?? '';
    
    if (empty($package_id)) {
        $errors['package'] = 'Please select a package';
    }
    
    if (empty($travel_date)) {
        $errors['travel_date'] = 'Travel date is required';
    } elseif (strtotime($travel_date) < strtotime('+7 days')) {
        $errors['travel_date'] = 'Travel date must be at least 7 days in advance';
    }
    
    if ($travelers < 1 || $travelers > 20) {
        $errors['travelers'] = 'Number of travelers must be between 1 and 20';
    }
    
    if (empty($errors)) {
        $pkg_query = "SELECT * FROM packages WHERE id = $package_id";
        $pkg_result = mysqli_query($conn, $pkg_query);
        $package = mysqli_fetch_assoc($pkg_result);
        
        $calculation = calculateBookingTotal($package['price'], $travelers, $user['loyalty_discount']);
        
        $query = "INSERT INTO bookings (user_id, package_id, package_name, travel_date, 
                  number_of_travelers, total_amount, discount_amount, final_amount, 
                  special_requests, status, payment_status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iissiddds", 
            $_SESSION['user_id'], $package_id, $package['name'], $travel_date,
            $travelers, $calculation['subtotal'], $calculation['discount'],
            $calculation['grand_total'], $special_requests
        );
        
        if (mysqli_stmt_execute($stmt)) {
            redirectWithMessage('bookings.php', 'Booking created successfully!', 'success');
        } else {
            $errors['general'] = 'Failed to create booking';
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h1>Create New Booking</h1>
    
    <?php if (isset($errors['general'])): ?>
        <div class="alert alert-error"><?php echo $errors['general']; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Select Package *</label>
            <select name="package_id" required>
                <option value="">-- Choose a package --</option>
                <?php 
                mysqli_data_seek($packages, 0);
                while($pkg = mysqli_fetch_assoc($packages)): 
                ?>
                <option value="<?php echo $pkg['id']; ?>" <?php echo isset($_POST['package_id']) && $_POST['package_id'] == $pkg['id'] ? 'selected' : ''; ?>>
                    <?php echo $pkg['name']; ?> - $<?php echo $pkg['price']; ?> (<?php echo $pkg['duration']; ?>)
                </option>
                <?php endwhile; ?>
            </select>
            <?php echo displayError($errors, 'package'); ?>
        </div>
        
        <div class="form-group">
            <label>Travel Date *</label>
            <input type="date" name="travel_date" min="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" 
                   value="<?php echo $_POST['travel_date'] ?? ''; ?>" required>
            <?php echo displayError($errors, 'travel_date'); ?>
        </div>
        
        <div class="form-group">
            <label>Number of Travelers *</label>
            <input type="number" name="travelers" min="1" max="20" 
                   value="<?php echo $_POST['travelers'] ?? 1; ?>" required>
            <?php echo displayError($errors, 'travelers'); ?>
        </div>
        
        <div class="form-group">
            <label>Special Requests</label>
            <textarea name="special_requests" rows="3"><?php echo $_POST['special_requests'] ?? ''; ?></textarea>
        </div>
        
        <?php if ($user['loyalty_discount'] > 0): ?>
            <div class="discount-info">
                <i class="fas fa-tag"></i>
                You qualify for <?php echo $user['loyalty_discount'] * 100; ?>% loyalty discount!
            </div>
        <?php endif; ?>
        
        <button type="submit" class="btn-primary">Create Booking</button>
        <a href="bookings.php" class="btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>