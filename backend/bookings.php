<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';
$is_admin = ($_SESSION['user_role'] ?? '') === 'admin';

// Get all bookings for this user (or all if admin)
if ($is_admin) {
    $query = "SELECT b.*, u.name as user_name FROM bookings b LEFT JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC";
    $result = mysqli_query($conn, $query);
    $bookings = $result;
} else {
    $query = "SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $bookings = mysqli_stmt_get_result($stmt);
}

// Calculate stats
$total_bookings = 0;
$total_spent = 0;
$active_bookings = 0;
$destinations_set = [];

if ($bookings && mysqli_num_rows($bookings) > 0) {
    mysqli_data_seek($bookings, 0);
    while($stat = mysqli_fetch_assoc($bookings)) {
        $total_bookings++;
        $total_spent += $stat['final_amount'];
        if ($stat['status'] == 'confirmed') {
            $active_bookings++;
        }
        if (!empty($stat['destination'])) {
            $destinations_set[$stat['destination']] = true;
        }
    }
    mysqli_data_seek($bookings, 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - EthioTrip</title>
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
        
        .bookings-container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { font-size: 1.8rem; color: #2d3436; }
        .page-header h1 i { color: #d4af37; margin-right: 10px; }
        .action-buttons { display: flex; gap: 1rem; }
        .btn-primary { display: inline-block; padding: 10px 20px; background: #d4af37; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.3s; }
        .btn-primary:hover { background: #c09c2c; transform: translateY(-2px); }
        .btn-secondary { display: inline-block; padding: 10px 20px; background: #2d3436; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.3s; }
        .btn-secondary:hover { background: #1a1a1a; transform: translateY(-2px); }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 15px; display: flex; align-items: center; gap: 1rem; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .stat-icon { width: 50px; height: 50px; background: linear-gradient(135deg, #d4af37, #f39c12); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; }
        .stat-info h3 { font-size: 1.5rem; font-weight: 700; color: #2d3436; }
        .stat-info p { font-size: 0.75rem; color: #999; }
        
        .bookings-table { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.08); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { background: #f8f9fa; padding: 15px; text-align: left; font-weight: 600; color: #555; font-size: 0.85rem; border-bottom: 1px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; color: #666; font-size: 0.85rem; }
        tr:hover { background: #fafafa; }
        
        .date-range-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
            margin: 2px;
        }
        .duration-badge {
            background: #fff3e0;
            color: #e65100;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status { padding: 4px 10px; border-radius: 50px; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #cce5ff; color: #004085; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .empty-state { text-align: center; padding: 3rem; background: white; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .empty-state i { font-size: 4rem; color: #ccc; margin-bottom: 1rem; }
        .empty-state p { color: #999; margin-bottom: 1.5rem; }
        
        .footer { background: #1a1a1a; color: #bbb; padding: 2rem 5%; text-align: center; margin-top: 3rem; }
        
        @media (max-width: 768px) {
            .bookings-container { padding: 1rem; }
            .page-header { flex-direction: column; text-align: center; }
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
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="logout.php">Logout</a></li>
            <li><span class="welcome-badge">Welcome, <?php echo htmlspecialchars($user_name); ?></span></li>
        </ul>
    </nav>

    <div class="bookings-container">
        <div class="page-header">
            <h1><i class="fas fa-suitcase"></i> My Travel Bookings</h1>
            <div class="action-buttons">
                <a href="../frontend/Destination.html" class="btn-primary"><i class="fas fa-map-marker-alt"></i> Choose Destination</a>
                <a href="../frontend/packages.html" class="btn-primary"><i class="fas fa-plus"></i> New Booking</a>
                <a href="dashboard.php" class="btn-secondary"><i class="fas fa-chart-line"></i> Dashboard</a>
            </div>
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
                <div class="stat-icon"><i class="fas fa-plane-departure"></i></div>
                <div class="stat-info">
                    <h3><?php echo $active_bookings; ?></h3>
                    <p>Active Trips</p>
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
                <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
                <div class="stat-info">
                    <h3><?php echo count($destinations_set); ?></h3>
                    <p>Destinations</p>
                </div>
            </div>
        </div>

        <?php if ($bookings && mysqli_num_rows($bookings) > 0): ?>
            <div class="bookings-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Package</th>
                            <th>Travel Dates</th>
                            <th>Duration</th>
                            <th>Travelers</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Booked On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = mysqli_fetch_assoc($bookings)): ?>
                        <tr>
                            <td><span style="font-weight: 600;">#<?php echo $booking['id']; ?></span></td>
                            <td><strong><?php echo htmlspecialchars($booking['package_name']); ?></strong></td>
                            <td>
                                <div class="date-range-badge">
                                    <i class="fas fa-calendar-alt"></i> 
                                    <?php echo date('M d, Y', strtotime($booking['start_date'])); ?>
                                </div>
                                <i class="fas fa-arrow-right" style="margin: 0 5px; color: #d4af37;"></i>
                                <div class="date-range-badge">
                                    <?php echo date('M d, Y', strtotime($booking['end_date'])); ?>
                                </div>
                             </td>
                            <td>
                                <div class="duration-badge">
                                    <i class="fas fa-clock"></i> <?php echo $booking['duration_days']; ?> days
                                </div>
                             </td>
                            <td><i class="fas fa-users" style="color: #d4af37; margin-right: 5px;"></i><?php echo $booking['number_of_travelers']; ?></td>
                            <td><span style="color: #d4af37; font-weight: 600;">$<?php echo number_format($booking['final_amount'], 2); ?></span></td>
                            <td><span class="status status-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                            <td>
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="edit-booking.php?id=<?php echo $booking['id']; ?>" class="btn-small btn-primary" style="background: #3498db; padding: 5px 10px; border-radius: 5px; color: white; text-decoration: none; font-size: 0.7rem;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete-booking.php?id=<?php echo $booking['id']; ?>" class="btn-small btn-danger" style="background: #e74c3c; padding: 5px 10px; border-radius: 5px; color: white; text-decoration: none; font-size: 0.7rem; margin-left: 5px;" onclick="return confirm('Cancel this booking?')">
                                        <i class="fas fa-trash"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-suitcase"></i>
                <p>You haven't made any bookings yet.</p>
                <p style="font-size: 0.8rem; margin-top: 5px;">Start your Ethiopian adventure today!</p>
                <a href="../frontend/Destination.html" class="btn-primary" style="margin-top: 1rem;"><i class="fas fa-map-marker-alt"></i> Choose a Destination</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>© <?php echo date('Y'); ?> EthioTrip Ethiopia. All rights reserved.</p>
    </footer>
</body>
</html>