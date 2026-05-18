<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';
$booking_id = $_GET['id'] ?? 0;
$errors = [];

// Get booking
$query = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    header('Location: bookings.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token first
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors['general'] = 'Security validation failed. Please try again.';
    } else {
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $travelers = intval($_POST['travelers'] ?? 1);
        $special_requests = $_POST['special_requests'] ?? '';
    
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
        $update = "UPDATE bookings SET start_date = ?, end_date = ?, duration_days = ?, 
                   number_of_travelers = ?, special_requests = ? 
                   WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "ssiisii", 
            $start_date, $end_date, $duration_days, 
            $travelers, $special_requests, $booking_id, $user_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Booking updated successfully!";
            $_SESSION['message_type'] = 'success';
            header('Location: bookings.php');
            exit();
        } else {
            $errors['general'] = 'Failed to update booking';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking - EthioTrip</title>
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
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 0.9rem; background: #f9f9f9; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #d4af37; background: white; }
        .form-group input[readonly] { background: #f0f0f0; cursor: not-allowed; }
        .error { color: #e74c3c; font-size: 0.8rem; margin-top: 5px; display: block; }
        .date-range { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .duration-info { background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-top: 0.5rem; text-align: center; }
        .btn-primary { padding: 12px 24px; background: #d4af37; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { background: #c09c2c; }
        .btn-secondary { padding: 12px 24px; background: #6c757d; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; margin-left: 10px; }
        .btn-secondary:hover { background: #5a6268; }
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
            <li><a href="../frontend/Destination.html">Destinations</a></li>
            <li><a href="../frontend/packages.html">Packages</a></li>
            <li><a href="bookings.php">My Bookings</a></li>
            <li><a href="logout.php">Logout</a></li>
            <li><span class="welcome-badge">Welcome, <?php echo safe($user_name); ?></span></li>
        </ul>
    </nav>

    <div class="form-container">
        <h1><i class="fas fa-edit"></i> Edit Booking #<?php echo $booking['id']; ?></h1>
        
        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $errors['general']; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php echo csrfField(); ?>

            <div class="form-group">
                <label>Package</label>
                <input type="text" value="<?php echo safe($booking['package_name']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label>Travel Dates *</label>
                <div class="date-range">
                    <div>
                        <label style="font-size: 0.8rem;">Start Date</label>
                        <input type="date" name="start_date" id="start_date" 
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo $_POST['start_date'] ?? $booking['start_date']; ?>" 
                               required onchange="updateDuration()">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem;">End Date</label>
                        <input type="date" name="end_date" id="end_date" 
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo $_POST['end_date'] ?? $booking['end_date']; ?>" 
                               required onchange="updateDuration()">
                    </div>
                </div>
                <div id="durationDisplay" class="duration-info">
                    <i class="fas fa-calendar-week"></i> Trip Duration: <strong id="durationDays"><?php echo $booking['duration_days']; ?></strong> days
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
                <input type="number" name="travelers" min="1" max="20" 
                       value="<?php echo $_POST['travelers'] ?? $booking['number_of_travelers']; ?>" required>
                <?php if (isset($errors['travelers'])): ?>
                    <span class="error"><?php echo $errors['travelers']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Special Requests</label>
                <textarea name="special_requests" rows="3"><?php echo safe($_POST['special_requests'] ?? $booking['special_requests']); ?></textarea>
            </div>
            
            <button type="submit" class="btn-primary">Update Booking</button>
            <a href="bookings.php" class="btn-secondary">Cancel</a>
        </form>
    </div>

    <footer class="footer">
        <p>© <?php echo date('Y'); ?> EthioTrip Ethiopia. All rights reserved.</p>
    </footer>

    <script>
        function updateDuration() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                if (diffDays > 0) {
                    document.getElementById('durationDays').innerText = diffDays;
                }
            }
        }
    </script>
</body>
</html>