<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';
$errors = [];

// Get user data for discount
$user_query = "SELECT loyalty_discount, trips_completed FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($user_result);
$discount_rate = $user_data['loyalty_discount'] ?? 0;
$discount_percent = $discount_rate * 100;

// Get packages
$packages_query = "SELECT * FROM packages WHERE is_active = 1";
$packages = mysqli_query($conn, $packages_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token first
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors['general'] = 'Security validation failed. Please try again.';
    } else {
        $package_id = $_POST['package_id'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $travelers = intval($_POST['travelers'] ?? 1);
        $special_requests = $_POST['special_requests'] ?? '';

        if (empty($package_id)) {
            $errors['package'] = 'Please select a package';
        }

        // Validate dates
        $today = date('Y-m-d');

        if (empty($start_date)) {
            $errors['start_date'] = 'Start date is required';
        } elseif (strtotime($start_date) < strtotime($today)) {
            $errors['start_date'] = 'Start date cannot be in the past';
        }

        if (empty($end_date)) {
            $errors['end_date'] = 'End date is required';
        } elseif (strtotime($end_date) < strtotime($start_date)) {
            $errors['end_date'] = 'End date must be after start date';
        }

        // Calculate duration in days
        $duration_days = 0;
        if (!empty($start_date) && !empty($end_date)) {
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $interval = $start->diff($end);
            $duration_days = $interval->days;

            if ($duration_days < 1) {
                $errors['end_date'] = 'Trip must be at least 1 day';
            }
            if ($duration_days > 30) {
                $errors['end_date'] = 'Trip cannot exceed 30 days';
            }
        }

        if ($travelers < 1 || $travelers > 20) {
            $errors['travelers'] = 'Number of travelers must be between 1 and 20';
        }

        if (empty($errors)) {
            $pkg_query = "SELECT * FROM packages WHERE id = ?";
            $stmt = mysqli_prepare($conn, $pkg_query);
            mysqli_stmt_bind_param($stmt, "i", $package_id);
            mysqli_stmt_execute($stmt);
            $pkg_result = mysqli_stmt_get_result($stmt);
            $package = mysqli_fetch_assoc($pkg_result);

            if ($package) {
                // Calculate price based on duration (daily rate)
                $daily_rate = $package['price'] / 3; // Default packages are 3 days
                $total_price = $daily_rate * $duration_days;
                $subtotal = $total_price * $travelers;
                $discount_amount = $subtotal * $discount_rate;
                $total_after_discount = $subtotal - $discount_amount;
                $tax = $total_after_discount * 0.10;
                $grand_total = $total_after_discount + $tax;

                $insert = "INSERT INTO bookings (user_id, package_id, package_name, start_date, end_date, duration_days,
                           number_of_travelers, total_amount, discount_amount, final_amount,
                           special_requests, status, payment_status)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')";
                $stmt = mysqli_prepare($conn, $insert);
                mysqli_stmt_bind_param($stmt, "iisssiiddds",
                    $user_id, $package_id, $package['name'], $start_date, $end_date, $duration_days,
                    $travelers, $subtotal, $discount_amount, $grand_total, $special_requests
                );

                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['message'] = "Booking created successfully!";
                    $_SESSION['message_type'] = 'success';
                    header('Location: bookings.php');
                    exit();
                } else {
                    $errors['general'] = 'Failed to create booking: ' . mysqli_error($conn);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Booking - EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f7fa; }
        
        .navbar {
            background: white;
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            flex-wrap: wrap;
            gap: 1rem;
        }
        .logo { font-size: 1.5rem; font-weight: 700; text-decoration: none; color: #2d3436; }
        .logo span { color: #d4af37; }
        .nav-links { display: flex; gap: 2rem; align-items: center; list-style: none; flex-wrap: wrap; }
        .nav-links a { text-decoration: none; color: #2d3436; font-weight: 500; transition: 0.3s; }
        .nav-links a:hover { color: #d4af37; }
        .welcome-badge { background: linear-gradient(135deg, #d4af37, #f39c12); padding: 0.5rem 1rem; border-radius: 50px; color: white; font-weight: 600; font-size: 0.9rem; }
        
        .form-container { max-width: 800px; margin: 2rem auto; background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .form-container h1 { margin-bottom: 1.5rem; color: #2d3436; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #555; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 0.9rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #d4af37; }
        .error { color: #e74c3c; font-size: 0.8rem; margin-top: 5px; display: block; }
        .discount-info { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: white; text-align: center; }
        .btn-primary { padding: 12px 24px; background: #d4af37; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { background: #c09c2c; }
        .btn-secondary { padding: 12px 24px; background: #6c757d; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; margin-left: 10px; }
        .btn-secondary:hover { background: #5a6268; }
        .date-range { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .duration-preview { background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-top: 0.5rem; text-align: center; }
        .footer { background: #1a1a1a; color: #bbb; padding: 2rem 5%; text-align: center; margin-top: 3rem; }
        
        @media (max-width: 768px) {
            .form-container { margin: 1rem; padding: 1.5rem; }
            .date-range { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="logo">Ethio<span>Trip</span></a>
        <ul class="nav-links">
            <li><a href="../frontend/home.html">Home</a></li>
            <li><a href="../frontend/destination.html">Destinations</a></li>
            <li><a href="../frontend/packages.html">Packages</a></li>
            <li><a href="bookings.php">My Bookings</a></li>
            <li><a href="logout.php">Logout</a></li>
            <li><span class="welcome-badge">Welcome, <?php echo safe($user_name); ?></span></li>
        </ul>
    </nav>

    <div class="form-container">
        <h1><i class="fas fa-plus-circle"></i> Create New Booking</h1>
        
        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $errors['general']; ?></div>
        <?php endif; ?>
        
        <?php if ($discount_percent > 0): ?>
            <div class="discount-info">
                <i class="fas fa-tag"></i> You qualify for <?php echo $discount_percent; ?>% loyalty discount!
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="bookingForm">
            <?php echo csrfField(); ?>

            <div class="form-group">
                <label>Select Package *</label>
                <select name="package_id" id="package_id" required onchange="updatePackageInfo()">
                    <option value="">-- Choose a package --</option>
                    <?php 
                    mysqli_data_seek($packages, 0);
                    while($pkg = mysqli_fetch_assoc($packages)): 
                    ?>
                    <option value="<?php echo $pkg['id']; ?>" 
                            data-price="<?php echo $pkg['price']; ?>"
                            data-name="<?php echo htmlspecialchars($pkg['name']); ?>"
                            data-duration="<?php echo $pkg['duration']; ?>"
                            <?php echo isset($_POST['package_id']) && $_POST['package_id'] == $pkg['id'] ? 'selected' : ''; ?>>
                        <?php echo $pkg['name']; ?> - $<?php echo $pkg['price']; ?> (<?php echo $pkg['duration']; ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
                <?php if (isset($errors['package'])): ?>
                    <span class="error"><?php echo $errors['package']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Travel Dates *</label>
                <div class="date-range">
                    <div>
                        <label style="font-size: 0.8rem;">Start Date</label>
                        <input type="date" name="start_date" id="start_date" 
                               min="<?php echo date('Y-m-d'); ?>" 
                               value="<?php echo $_POST['start_date'] ?? ''; ?>" 
                               required onchange="calculateDuration()">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem;">End Date</label>
                        <input type="date" name="end_date" id="end_date" 
                               min="<?php echo date('Y-m-d'); ?>" 
                               value="<?php echo $_POST['end_date'] ?? ''; ?>" 
                               required onchange="calculateDuration()">
                    </div>
                </div>
                <div id="durationDisplay" class="duration-preview" style="display: none;">
                    <i class="fas fa-calendar-week"></i> Trip Duration: <strong id="durationDays">0</strong> days
                </div>
                <?php if (isset($errors['start_date'])): ?>
                    <span class="error"><?php echo $errors['start_date']; ?></span>
                <?php endif; ?>
                <?php if (isset($errors['end_date'])): ?>
                    <span class="error"><?php echo $errors['end_date']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Number of Travelers *</label>
                <input type="number" name="travelers" id="travelers" min="1" max="20" 
                       value="<?php echo $_POST['travelers'] ?? 1; ?>" required onchange="updatePriceEstimate()">
                <?php if (isset($errors['travelers'])): ?>
                    <span class="error"><?php echo $errors['travelers']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Price Estimate</label>
                <div style="background: #f0f0f0; padding: 15px; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Daily Rate:</span>
                        <span id="dailyRate">$0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Duration:</span>
                        <span id="estimateDuration">0 days</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Subtotal:</span>
                        <span id="estimateSubtotal">$0.00</span>
                    </div>
                    <?php if ($discount_percent > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: #d4af37;">
                        <span>Loyalty Discount (<?php echo $discount_percent; ?>%):</span>
                        <span id="estimateDiscount">-$0.00</span>
                    </div>
                    <?php endif; ?>
                    <div style="display: flex; justify-content: space-between; margin-top: 8px; padding-top: 8px; border-top: 1px solid #ddd; font-weight: bold;">
                        <span>Total (incl. 10% VAT):</span>
                        <span id="estimateTotal" style="color: #d4af37;">$0.00</span>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Special Requests</label>
                <textarea name="special_requests" rows="3" placeholder="Any special requirements or requests? (e.g., dietary needs, hotel preferences, accessibility requirements)"><?php echo safe($_POST['special_requests'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="btn-primary">Create Booking</button>
            <a href="bookings.php" class="btn-secondary">Cancel</a>
        </form>
    </div>

    <footer class="footer">
        <p>© <?php echo date('Y'); ?> EthioTrip Ethiopia. All rights reserved.</p>
    </footer>

    <script>
        let currentPackagePrice = 0;
        let currentPackageName = '';
        let discountRate = <?php echo $discount_rate; ?>;
        let discountPercent = <?php echo $discount_percent; ?>;
        
        function updatePackageInfo() {
            const select = document.getElementById('package_id');
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption.value) {
                currentPackagePrice = parseFloat(selectedOption.dataset.price);
                currentPackageName = selectedOption.dataset.name;
                // Default duration (standard packages are 3 days)
                const dailyRate = currentPackagePrice / 3;
                document.getElementById('dailyRate').innerText = '$' + dailyRate.toFixed(2);
                calculateDuration();
            } else {
                currentPackagePrice = 0;
                document.getElementById('dailyRate').innerText = '$0.00';
            }
        }
        
        function calculateDuration() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const durationDisplay = document.getElementById('durationDisplay');
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                if (diffDays > 0) {
                    document.getElementById('durationDays').innerText = diffDays;
                    durationDisplay.style.display = 'block';
                    updatePriceEstimate();
                } else {
                    durationDisplay.style.display = 'none';
                }
            } else {
                durationDisplay.style.display = 'none';
            }
        }
        
        function updatePriceEstimate() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const travelers = parseInt(document.getElementById('travelers').value) || 1;
            
            if (startDate && endDate && currentPackagePrice > 0) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const diffDays = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24));
                
                if (diffDays > 0) {
                    const dailyRate = currentPackagePrice / 3;
                    const subtotal = dailyRate * diffDays * travelers;
                    const discount = subtotal * discountRate;
                    const afterDiscount = subtotal - discount;
                    const tax = afterDiscount * 0.10;
                    const total = afterDiscount + tax;
                    
                    document.getElementById('estimateDuration').innerText = diffDays + ' days';
                    document.getElementById('estimateSubtotal').innerText = '$' + subtotal.toFixed(2);
                    if (discountRate > 0) {
                        document.getElementById('estimateDiscount').innerText = '-$' + discount.toFixed(2);
                    }
                    document.getElementById('estimateTotal').innerText = '$' + total.toFixed(2);
                }
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updatePackageInfo();
        });
    </script>
</body>
</html>