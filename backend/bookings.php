<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'My Bookings';
$user_id = $_SESSION['user_id'];

$bookings = getUserBookings($user_id);

include 'includes/header.php';
?>

<div class="bookings-page">
    <h1>My Bookings</h1>
    
    <div class="action-bar">
        <a href="create-booking.php" class="btn-primary">+ New Booking</a>
        <a href="../frontend/packages.html" class="btn-secondary">Browse Packages</a>
    </div>
    
    <?php if (mysqli_num_rows($bookings) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Package</th>
                    <th>Travel Date</th>
                    <th>Travelers</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($booking = mysqli_fetch_assoc($bookings)): ?>
                <tr>
                    <td>#<?php echo $booking['id']; ?></td>
                    <td><?php echo htmlspecialchars($booking['package_name']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></td>
                    <td><?php echo $booking['number_of_travelers']; ?></td>
                    <td>$<?php echo number_format($booking['final_amount'], 2); ?></td>
                    <td>
                        <span class="status status-<?php echo $booking['status']; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status status-<?php echo $booking['payment_status']; ?>">
                            <?php echo ucfirst($booking['payment_status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($booking['status'] == 'pending'): ?>
                            <a href="edit-booking.php?id=<?php echo $booking['id']; ?>" class="btn-small">Edit</a>
                            <a href="delete-booking.php?id=<?php echo $booking['id']; ?>" 
                               class="btn-small btn-danger" 
                               onclick="return confirm('Cancel this booking?')">Cancel</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">
            <p>You haven't made any bookings yet.</p>
            <a href="../frontend/packages.html" class="btn-primary">Explore Packages</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
