<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details | EthioTrip Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require __DIR__ . '/../partials/admin_head.php'; ?>
</head>
<body>
    <div class="page">
        <?php require __DIR__ . '/../partials/admin_navbar.php'; ?>
        <div class="header"><h1>Booking #<?php echo $booking['id']; ?></h1></div>
        <div class="card">
            <?php foreach ($booking as $key => $value): ?>
                <div class="row full" style="margin-bottom:12px;"><strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong> <?php echo htmlspecialchars($value ?? 'N/A'); ?></div>
            <?php endforeach; ?>
            <a href="/ethiotrip1/ethiotrip/public/admin/bookings" class="button" style="margin-top:16px;">Back to Payments</a>
        </div>
    </div>
    <?php require __DIR__ . '/../partials/admin_footer_scripts.php'; ?>
</body>
</html>
