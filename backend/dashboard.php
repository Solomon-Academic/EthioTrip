<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$user = getCurrentUser();
$user_id = $_SESSION['user_id'];

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as active_bookings,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_trips,
    COALESCE(SUM(final_amount), 0) as total_spent
    FROM bookings WHERE user_id = $user_id AND status != 'cancelled'";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get recent bookings
$recent_query = "SELECT * FROM bookings WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$recent_bookings = mysqli_query($conn, $recent_query);

$loyalty_points = floor(($stats['total_spent'] ?? 0) / 10);
$next_tier_points = 100 - ($loyalty_points % 100);
$discount_percent = ($user['loyalty_discount'] ?? 0) * 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .navbar { background: white; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 20px rgba(0,0,0,0.08); flex-wrap: wrap; gap: 1rem; }
        .logo { font-size: 1.5rem; font-weight: 700; text-decoration: none; color: #2d3436; }
        .logo span { color: #d4af37; }
        .nav-links { display: flex; gap: 2rem; align-items: center; list-style: none; flex-wrap: wrap; }
        .nav-links a { text-decoration: none; color: #2d3436; font-weight: 500; transition: 0.3s; }
        .nav-links a:hover { color: #d4af37; }
        .welcome-badge { background: linear-gradient(135deg, #d4af37, #f39c12); padding: 0.5rem 1rem; border-radius: 50px; color: white; font-weight: 600; font-size: 0.9rem; }
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem 5%; }
        .hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 30px; padding: 3rem; margin-bottom: 2rem; color: white; }
        .hero-section h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 20px; display: flex; align-items: center; gap: 1rem; transition: transform 0.3s; cursor: pointer; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
        .stat-icon { width: 60px; height: 60px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: white; }
        .stat-info h3 { font-size: 1.8rem; font-weight: 700; color: #2d3436; }
        .stat-info p { color: #666; font-size: 0.85rem; }
        .discount-banner { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .discount-info { display: flex; align-items: center; gap: 1rem; }
        .discount-info i { font-size: 2rem; color: white; }
        .discount-info h3 { color: white; font-size: 1.3rem; }
        .discount-percent { background: white; padding: 0.5rem 1.5rem; border-radius: 50px; font-weight: 700; font-size: 1.5rem; color: #f5576c; }
        .loyalty-section { background: white; border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem; }
        .loyalty-header { display: flex; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem; }
        .progress-bar { background: #e0e0e0; border-radius: 10px; height: 10px; overflow: hidden; }
        .progress-fill { background: linear-gradient(90deg, #d4af37, #f39c12); width: 0%; height: 100%; border-radius: 10px; transition: width 0.5s; }
        .section-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .section-title h2 { font-size: 1.5rem; color: #2d3436; }
        .bookings-table { background: white; border-radius: 20px; overflow-x: auto; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 1rem; text-align: left; font-weight: 600; color: #555; }
        td { padding: 1rem; border-bottom: 1px solid #eee; color: #666; }
        .status { padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #cce5ff; color: #004085; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .btn-sm { padding: 0.25rem 0.75rem; background: #d4af37; color: white; text-decoration: none; border-radius: 5px; font-size: 0.8rem; display: inline-block; }
        .btn-sm:hover { background: #c09c2c; }
        .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 2rem; }
        .action-card { background: white; padding: 1rem; border-radius: 15px; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; color: #2d3436; font-weight: 500; transition: 0.3s; }
        .action-card:hover { background: #d4af37; color: white; transform: translateY(-3px); }
        .empty-state { text-align: center; padding: 3rem; color: #999; }
        .btn-primary { display: inline-block; margin-top: 1rem; padding: 0.5rem 1.5rem; background: #d4af37; color: white; text-decoration: none; border-radius: 50px; }
        .footer { background: #1a1a1a; color: #bbb; padding: 2rem 5%; text-align: center; margin-top: 3rem; }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; }
            .nav-links { justify-content: center; }
            .stats-grid { grid-template-columns: 1fr; }
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
        <li><span class="welcome-badge">Welcome, <?php echo htmlspecialchars($user['name'] ?? ''); ?></span></li>
    </ul>
</nav>
<div class="container">
    <div class="hero-section">
        <h1>Welcome back, <?php echo htmlspecialchars($user['name'] ?? ''); ?>! 👋</h1>
        <p>Your Ethiopian adventure dashboard - manage your journeys, track rewards, and plan your next escape.</p>
    </div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-info">
                <h3><?php echo $stats['total_bookings'] ?? 0; ?></h3>
                <p>Total Bookings</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-plane-departure"></i></div>
            <div class="stat-info">
                <h3><?php echo $stats['active_bookings'] ?? 0; ?></h3>
                <p>Active Trips</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-info">
                <h3>$<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></h3>
                <p>Total Spent</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-gem"></i></div>
            <div class="stat-info">
                <h3><?php echo $loyalty_points; ?></h3>
                <p>Loyalty Points</p>
            </div>
        </div>
    </div>
    <?php if ($discount_percent > 0): ?>
    <div class="discount-banner">
        <div class="discount-info">
            <i class="fas fa-tag"></i>
            <div>
                <h3>Loyalty Discount Active!</h3>
                <p>You're enjoying special rates on all bookings</p>
            </div>
        </div>
        <div class="discount-percent"><?php echo $discount_percent; ?>% OFF</div>
    </div>
    <?php endif; ?>
    <div class="loyalty-section">
        <div class="loyalty-header">
            <div>
                <strong>Loyalty Program</strong>
                <p style="font-size: 0.8rem; color: #666;">Earn points with every booking</p>
            </div>
            <div>
                <strong><?php echo $loyalty_points; ?> points</strong>
                <p style="font-size: 0.8rem; color: #666;"><?php echo max($next_tier_points, 0); ?> points to next tier</p>
            </div>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo min(($loyalty_points / 100) * 100, 100); ?>%;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.7rem; color: #999;">
            <span>Bronze</span><span>Silver (100 pts)</span><span>Gold (250 pts)</span><span>Platinum (500 pts)</span>
        </div>
    </div>
    <div class="section-title">
        <h2><i class="fas fa-history"></i> Recent Bookings</h2>
        <a href="bookings.php" style="color: #d4af37; text-decoration: none;">View All →</a>
    </div>
    <div class="bookings-table">
        <?php if (mysqli_num_rows($recent_bookings) > 0): ?>
        <table>
            <thead><tr><th>Package</th><th>Travel Date</th><th>Travelers</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                <?php while($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($booking['package_name'] ?? ''); ?></strong></td>
                    <td><?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></td>
                    <td><?php echo $booking['number_of_travelers']; ?> person(s)</td>
                    <td>$<?php echo number_format($booking['final_amount'], 2); ?></td>
                    <td><span class="status status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                    <td><a href="edit-booking.php?id=<?php echo $booking['id']; ?>" class="btn-sm">View</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-suitcase"></i>
            <p>You haven't made any bookings yet.</p>
            <a href="../frontend/packages.html" class="btn-primary">Start Your Journey →</a>
        </div>
        <?php endif; ?>
    </div>
    <div class="quick-actions">
        <a href="../frontend/packages.html" class="action-card"><i class="fas fa-plus-circle"></i> Book a Package</a>
        <a href="../frontend/Destination.html" class="action-card"><i class="fas fa-compass"></i> Explore Destinations</a>
        <a href="bookings.php" class="action-card"><i class="fas fa-calendar-alt"></i> My Bookings</a>
        <a href="logout.php" class="action-card"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
    </div>
</div>
<footer class="footer">
    <p>© <?php echo date('Y'); ?> EthioTrip Ethiopia. All rights reserved.</p>
</footer>
</body>
</html>