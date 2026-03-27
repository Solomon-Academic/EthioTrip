<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get user's bookings
$stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
$total_bookings = $bookings->num_rows;

// Calculate total spent
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM bookings WHERE user_id = ? AND status = 'confirmed'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_spent = $result->fetch_assoc()['total'] ?? 0;

// Get recent bookings (last 5)
$stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_date DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_bookings = $stmt->get_result();

include 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <p>Manage your Ethiopian adventures with EthioTrip</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-suitcase"></i>
            <div class="stat-number"><?php echo $total_bookings; ?></div>
            <span>Total Bookings</span>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-dollar-sign"></i>
            <div class="stat-number">$<?php echo number_format($total_spent, 2); ?></div>
            <span>Total Spent</span>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-check-circle"></i>
            <div class="stat-number"><?php echo $total_bookings; ?></div>
            <span>Active Trips</span>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-star"></i>
            <div class="stat-number">5</div>
            <span>Loyalty Points</span>
        </div>
    </div>

    <div class="dashboard-sections">
        <div class="recent-bookings">
            <h2>Recent Bookings</h2>
            <?php if ($recent_bookings->num_rows > 0): ?>
                <div class="bookings-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Package</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['package_name']); ?></td>
                                <td>$<?php echo number_format($booking['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($booking['payment_method']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view-booking.php?id=<?php echo $booking['id']; ?>" class="btn-sm">View</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-bookings">You haven't made any bookings yet. <a href="packages.html">Start your journey now!</a></p>
            <?php endif; ?>
        </div>

        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="packages.html" class="action-btn">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Book a Package</span>
                </a>
                <a href="destinations.html" class="action-btn">
                    <i class="fas fa-mountain"></i>
                    <span>Explore Destinations</span>
                </a>
                <a href="profile.php" class="action-btn">
                    <i class="fas fa-user"></i>
                    <span>Edit Profile</span>
                </a>
                <a href="logout.php" class="action-btn logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sign Out</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 40px;
}

.dashboard-header h1 {
    font-size: 2rem;
    color: var(--primary);
    margin-bottom: 10px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card i {
    font-size: 2.5rem;
    color: var(--accent);
    margin-bottom: 15px;
}

.stat-number {
    font-size: 2rem;
    font-weight: 600;
    color: var(--primary);
}

.dashboard-sections {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
}

.recent-bookings {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.recent-bookings h2, .quick-actions h2 {
    margin-bottom: 20px;
    color: var(--primary);
}

.bookings-table {
    overflow-x: auto;
}

.bookings-table table {
    width: 100%;
    border-collapse: collapse;
}

.bookings-table th,
.bookings-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.bookings-table th {
    background: #f8f9fa;
    font-weight: 600;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-confirmed {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.btn-sm {
    display: inline-block;
    padding: 5px 12px;
    background: var(--accent);
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.85rem;
    transition: 0.3s;
}

.btn-sm:hover {
    background: #b8941a;
}

.quick-actions {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    background: #f8f9fa;
    text-decoration: none;
    color: var(--primary);
    border-radius: 10px;
    transition: 0.3s;
}

.action-btn:hover {
    background: var(--accent);
    color: white;
    transform: translateX(5px);
}

.action-btn i {
    font-size: 1.2rem;
}

.logout-btn:hover {
    background: #dc3545;
}

.no-bookings {
    text-align: center;
    padding: 40px;
    color: #666;
}

.no-bookings a {
    color: var(--accent);
    text-decoration: none;
    font-weight: 600;
}

@media (max-width: 768px) {
    .dashboard-sections {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>