<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking #<?php echo (int) ($booking['id'] ?? 0); ?> | EthioTrip Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require __DIR__ . '/../partials/admin_head.php'; ?>
</head>
<body>
    <div class="page">
        <?php require __DIR__ . '/../partials/admin_navbar.php'; ?>
        <div class="header">
            <h1>Booking #<?php echo (int) ($booking['id'] ?? 0); ?></h1>
            <p>Customer: <?php echo htmlspecialchars($booking['user_name'] ?? 'Unknown'); ?> &lt;<?php echo htmlspecialchars($booking['user_email'] ?? ''); ?>&gt;</p>
        </div>
        <div class="card">
            <div class="grid-cols">
                <div><strong>Destination</strong><br><?php echo htmlspecialchars($booking['destination'] ?? '—'); ?></div>
                <div><strong>Package</strong><br><?php echo htmlspecialchars($booking['package_name'] ?? '—'); ?></div>
                <div><strong>Travel dates</strong><br><?php echo htmlspecialchars($booking['start_date'] ?? ''); ?> → <?php echo htmlspecialchars($booking['end_date'] ?? ''); ?></div>
                <div><strong>Travelers</strong><br><?php echo (int) ($booking['number_of_travelers'] ?? 1); ?></div>
                <div><strong>Payment status</strong><br><?php echo ucfirst(htmlspecialchars($booking['payment_status'] ?? 'pending')); ?></div>
                <div><strong>Approval status</strong><br><?php echo ucfirst(htmlspecialchars($booking['admin_approval_status'] ?? 'pending')); ?></div>
                <div><strong>Total</strong><br>$<?php echo number_format((float) ($booking['final_amount'] ?? 0), 2); ?></div>
                <div><strong>Transaction ID</strong><br><?php echo htmlspecialchars($booking['transaction_id'] ?? '—'); ?></div>
                <?php if (!empty($booking['customer_notified_at'])): ?>
                <div><strong>Last notified</strong><br><?php echo htmlspecialchars($booking['customer_notified_at']); ?> (<?php echo htmlspecialchars($booking['last_notification_type'] ?? ''); ?>)</div>
                <?php endif; ?>
            </div>
            <?php if (!empty($booking['special_requests'])): ?>
                <p style="margin-top:20px;"><strong>Special requests:</strong><br><?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($booking['admin_notes'])): ?>
                <p style="margin-top:16px;"><strong>Admin notes:</strong><br><?php echo nl2br(htmlspecialchars($booking['admin_notes'])); ?></p>
            <?php endif; ?>
            <a href="/ethiotrip1/ethiotrip/public/admin/bookings" class="button" style="margin-top:24px;">Back to payment review</a>
        </div>
    </div>
    <?php require __DIR__ . '/../partials/admin_footer_scripts.php'; ?>
</body>
</html>
