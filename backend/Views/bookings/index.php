<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ethiotrip1/ethiotrip/public/css/backend.css">
</head>
<body>
    <div class="page">
        <div class="navbar">
            <a href="/ethiotrip1/ethiotrip/public/dashboard" class="logo">Ethio<span style="color:#d4af37;">Trip</span></a>
            <div class="nav">
                <a href="/ethiotrip1/ethiotrip/public/dashboard">Dashboard</a>
                <a href="/ethiotrip1/ethiotrip/public/logout">Logout</a>
            </div>
        </div>

        <div class="hero">
            <div>
                <h1>My Bookings</h1>
                <p>You have <?php echo $stats['total_bookings']; ?> booking(s).</p>
            </div>
            <a class="button" href="/ethiotrip1/ethiotrip/public/bookings/create">Create Booking</a>
        </div>

        <div class="card">
            <?php if ($bookings instanceof \mysqli_result && $bookings->num_rows > 0): ?>
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
            <?php else: ?>
                <p>No bookings found yet. Start your next adventure from the dashboard.</p>
            <?php endif; ?>
        </div>
        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
        </footer>
    </div>
</body>
</html>
