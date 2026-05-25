<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require __DIR__ . '/../partials/user_head.php'; ?>
</head>
<body>
    <div class="page">
        <?php require __DIR__ . '/../partials/user_navbar.php'; ?>

        <div class="hero">
            <div>
                <h1>My Bookings</h1>
                <p>You have <?php echo $stats['total_bookings']; ?> booking(s).</p>
            </div>
            <a class="button" href="/ethiotrip1/ethiotrip/public/bookings/create">Create Booking</a>
        </div>

        <div class="card">
            <?php if ($bookings instanceof \mysqli_result && $bookings->num_rows > 0): ?>
                <div class="table-wrap admin-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Package</th>
                            <th>Dates</th>
                            <th>Travelers</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['package_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['start_date']); ?> → <?php echo htmlspecialchars($booking['end_date']); ?></td>
                                <td><?php echo $booking['number_of_travelers']; ?></td>
                                <td>$<?php echo number_format($booking['final_amount'], 2); ?></td>
                                <td><span class="status status-<?php echo htmlspecialchars($booking['status']); ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                <td class="actions">
                                    <a href="/ethiotrip1/ethiotrip/public/bookings/edit?id=<?php echo $booking['id']; ?>">Edit</a>
                                    <form method="POST" action="/ethiotrip1/ethiotrip/public/bookings/delete?id=<?php echo $booking['id']; ?>" style="display:inline;" onsubmit="return confirm('Cancel this booking?');">
                                        <?php echo $this->csrfField(); ?>
                                        <button type="submit" class="link-button">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <p>No bookings found yet. Start your next adventure from the dashboard.</p>
            <?php endif; ?>
        </div>
        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
        </footer>
    </div>
    <script src="/ethiotrip1/ethiotrip/public/js/nav-mobile.js"></script>
</body>
</html>
