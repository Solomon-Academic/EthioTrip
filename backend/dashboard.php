<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

// Get user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Get booking stats
$stats_query = "SELECT COUNT(*) as total, SUM(final_amount) as spent FROM bookings WHERE user_id = ? AND status = 'confirmed'";
$stmt = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$stats = mysqli_stmt_get_result($stmt);
$stats_data = mysqli_fetch_assoc($stats);

$total_bookings = $stats_data['total'] ?? 0;
$total_spent = $stats_data['spent'] ?? 0;
$discount_percent = ($user['loyalty_discount'] ?? 0) * 100;
$trips = $user['trips_completed'] ?? 0;
$is_admin = ($user['role'] ?? 'user') === 'admin';

// Get next tier info
$next_tier_query = "SELECT min_trips, tier_name, discount_percent FROM discount_tiers 
                     WHERE is_active = 1 AND min_trips > ? 
                     ORDER BY min_trips ASC LIMIT 1";
$stmt = mysqli_prepare($conn, $next_tier_query);
mysqli_stmt_bind_param($stmt, "i", $trips);
mysqli_stmt_execute($stmt);
$next_tier_result = mysqli_stmt_get_result($stmt);
$next_tier = mysqli_fetch_assoc($next_tier_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EthioTrip</title>
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
        .admin-badge { background: #e74c3c; margin-left: 10px; font-size: 0.7rem; padding: 3px 8px; }
        
        .dashboard-container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 2rem; margin-bottom: 2rem; color: white; }
        .hero-section h1 { font-size: 1.8rem; margin-bottom: 0.5rem; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 15px; display: flex; align-items: center; gap: 1rem; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .stat-icon { width: 60px; height: 60px; background: linear-gradient(135deg, #d4af37, #f39c12); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: white; }
        .stat-info h3 { font-size: 1.8rem; font-weight: 700; color: #2d3436; }
        .stat-info p { font-size: 0.8rem; color: #999; }
        
        .discount-card { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 15px; padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; color: white; }
        .discount-card .discount-value { font-size: 2rem; font-weight: 700; background: white; color: #f5576c; padding: 0.5rem 1.5rem; border-radius: 50px; }
        
        .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 2rem; }
        .action-card { background: white; padding: 1.5rem; border-radius: 15px; text-align: center; text-decoration: none; transition: 0.3s; display: block; }
        .action-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .action-card i { font-size: 2rem; color: #d4af37; margin-bottom: 0.5rem; display: block; }
        .action-card span { color: #2d3436; font-weight: 600; }
        
        .footer { background: #1a1a1a; color: #bbb; padding: 2rem 5%; text-align: center; margin-top: 3rem; }
        
        @media (max-width: 768px) {
            .dashboard-container { padding: 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
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
            <?php if ($is_admin): ?>
                <li><a href="discounts.php">Admin Panel</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout</a></li>
            <li><span class="welcome-badge"><?php echo safe($user_name); ?></span></li>
        </ul>
    </nav>

    <div class="dashboard-container">
        <div class="hero-section">
            <h1>Welcome, <?php echo safe($user_name); ?>! 👋</h1>
            <p>Your Ethiopian adventure dashboard - manage your journeys and track rewards.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-info">
                    <h3><?php echo $total_bookings; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($total_spent, 2); ?></h3>
                    <p>Total Spent</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                <div class="stat-info">
                    <h3><?php echo $trips; ?></h3>
                    <p>Trips Completed</p>
                </div>
            </div>
        </div>

        <?php if ($discount_percent > 0): ?>
        <div class="discount-card">
            <div>
                <i class="fas fa-tag"></i> Loyalty Discount Active!
                <p style="font-size: 0.8rem; margin-top: 5px;">Enjoy special rates on all bookings</p>
            </div>
            <div class="discount-value"><?php echo $discount_percent; ?>% OFF</div>
        </div>
        <?php endif; ?>

        <?php if ($next_tier && $next_tier['min_trips'] > $trips): ?>
        <div style="background: white; border-radius: 15px; padding: 1.5rem; margin-bottom: 2rem;">
            <strong>🎯 Next Tier Progress</strong>
            <div style="margin-top: 10px;">
                <div style="background: #e0e0e0; border-radius: 10px; height: 10px; overflow: hidden;">
                    <div style="background: #d4af37; width: <?php echo min(($trips / $next_tier['min_trips']) * 100, 100); ?>%; height: 100%;"></div>
                </div>
                <p style="margin-top: 10px; font-size: 0.8rem; color: #666;">
                    <?php echo ($next_tier['min_trips'] - $trips); ?> more trip(s) to reach <?php echo $next_tier['tier_name']; ?> (<?php echo $next_tier['discount_percent']; ?>% off)
                </p>
            </div>
        </div>
        <?php endif; ?>

        <div class="quick-actions">
            <a href="../frontend/packages.html" class="action-card">
                <i class="fas fa-plus-circle"></i>
                <span>Book a Package</span>
            </a>
            <a href="../frontend/destination.html" class="action-card">
                <i class="fas fa-compass"></i>
                <span>Explore Destinations</span>
            </a>
            <a href="bookings.php" class="action-card">
                <i class="fas fa-calendar-alt"></i>
                <span>My Bookings</span>
            </a>
            <?php if ($is_admin): ?>
            <a href="discounts.php" class="action-card">
                <i class="fas fa-tags"></i>
                <span>Manage Discounts</span>
            </a>
            <?php endif; ?>
            <a href="logout.php" class="action-card">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </div>

    <footer class="footer">
        <p>© <?php echo date('Y'); ?> EthioTrip Ethiopia. All rights reserved.</p>
    </footer>
</body>
</html>