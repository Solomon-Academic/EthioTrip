<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require __DIR__ . '/../partials/user_head.php'; ?>
    <style>
        .status-hint { font-size: 0.8rem; color: #636e72; display: block; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="page">
        <?php require __DIR__ . '/../partials/user_navbar.php'; ?>

        <div class="hero">
            <div>
                <h1>My bookings</h1>
                <p>Track payment review and trip confirmation status for each reservation.</p>
            </div>
            <a class="button" href="/ethiotrip1/ethiotrip/public/destination">Book a new trip</a>
        </div>

        <div class="card">
            <?php if ($bookings instanceof \mysqli_result && $bookings->num_rows > 0): ?>
                <div class="table-wrap admin-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Trip</th>
                            <th>Dates</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Booking</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <?php
                            $paymentStatus = $booking['payment_status'] ?? 'pending';
                            $approvalStatus = $booking['admin_approval_status'] ?? 'pending';
                            $paymentHint = match ($paymentStatus) {
                                'completed' => 'Payment verified',
                                'failed' => 'Payment not approved',
                                default => 'Awaiting admin review',
                            };
                            $approvalHint = match ($approvalStatus) {
                                'approved' => 'Trip confirmed',
                                'rejected' => 'Not confirmed',
                                default => 'Pending review',
                            };
                            ?>
                            <tr>
                                <td>#<?php echo (int) $booking['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($booking['package_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($booking['destination'] ?? ''); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($booking['start_date']); ?> → <?php echo htmlspecialchars($booking['end_date']); ?></td>
                                <td>$<?php echo number_format((float) $booking['final_amount'], 2); ?></td>
                                <td>
                                    <span class="status status-<?php echo htmlspecialchars($paymentStatus === 'completed' ? 'confirmed' : ($paymentStatus === 'failed' ? 'cancelled' : 'pending')); ?>">
                                        <?php echo ucfirst(htmlspecialchars($paymentStatus)); ?>
                                    </span>
                                    <span class="status-hint"><?php echo htmlspecialchars($paymentHint); ?></span>
                                </td>
                                <td>
                                    <span class="status status-<?php echo htmlspecialchars($approvalStatus === 'approved' ? 'confirmed' : ($approvalStatus === 'rejected' ? 'cancelled' : 'pending')); ?>">
                                        <?php echo ucfirst(htmlspecialchars($approvalStatus)); ?>
                                    </span>
                                    <span class="status-hint"><?php echo htmlspecialchars($approvalHint); ?></span>
                                </td>
                                <td class="actions">
                                    <?php if ($approvalStatus !== 'approved' && $paymentStatus === 'pending'): ?>
                                        <span class="status-hint">Confirmation email sent after payment approval.</span>
                                    <?php elseif ($approvalStatus === 'approved'): ?>
                                        <span class="status-hint" style="color:#176b38;">Check your inbox for confirmation details.</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <p>No bookings yet. <a href="/ethiotrip1/ethiotrip/public/destination">Explore destinations</a> to start planning your journey.</p>
            <?php endif; ?>
        </div>
        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia.</div>
        </footer>
    </div>
    <script src="/ethiotrip1/ethiotrip/public/js/nav-mobile.js"></script>
</body>
</html>
