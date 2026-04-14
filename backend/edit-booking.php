<?php 
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id <= 0) {
    header('Location: bookings.php');
    exit();
}

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
        $update = "UPDATE bookings SET travel_date = ?, number_of_travelers = ?, special_requests = ? WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "sisii", $travel_date, $travelers, $special_requests, $booking_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Booking updated successfully!";
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
        .form-group input[disabled] { background: #f0f0f0; cursor: not-allowed; }
        .error { color: #e74c3c; font-size: 0.8rem; margin-top: 5px; display: block; }
        .btn-primary { padding: 12px 24px; background: #d4af37; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { background: #c09c2c; }
        .btn-secondary { padding: 12px 24px; background: #6c757d; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; margin-left: 10px; }
        .btn-secondary:hover { background: #5a6268; }
        .footer { background: #1a1a1a; color: #bbb; padding: 2rem 5%; text-align: center; margin-top: 3rem; }
        
        @media (max-width: 768px) {
            .form-container { margin: 1rem; padding: 1.5rem; }
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
            <li><span class="welcome-badge">Welcome, <?php echo htmlspecialchars($user_name); ?></span></li>
        </ul>
    </nav>

    <div class="form-container">
        <h1><i class="fas fa-edit"></i> Edit Booking #<?php echo $booking['id']; ?></h1>
        
        <?php if (isset($errors['general'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $errors['general']; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Package</label>
                <input type="text" value="<?php echo htmlspecialchars($booking['package_name']); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label>Travel Date *</label>
                <input type="date" name="travel_date" value="<?php echo $_POST['travel_date'] ?? $booking['travel_date']; ?>" required>
                <?php if (isset($errors['travel_date'])): ?>
                    <span class="error"><?php echo $errors['travel_date']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Number of Travelers *</label>
                <input type="number" name="travelers" min="1" max="20" value="<?php echo $_POST['travelers'] ?? $booking['number_of_travelers']; ?>" required>
                <?php if (isset($errors['travelers'])): ?>
                    <span class="error"><?php echo $errors['travelers']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Special Requests</label>
                <textarea name="special_requests" rows="3"><?php echo $_POST['special_requests'] ?? $booking['special_requests']; ?></textarea>
            </div>
            
            <button type="submit" class="btn-primary">Update Booking</button>
            <a href="bookings.php" class="btn-secondary">Cancel</a>
        </form>
    </div>

    <footer class="footer">
        <p>© <?php echo date('Y'); ?> EthioTrip Ethiopia. All rights reserved.</p>
    </footer>
</body>
</html>
