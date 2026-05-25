<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Details | EthioTrip Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ethiotrip1/ethiotrip/public/css/backend.css">
</head>
<body>
    <div class="page">
        <div class="navbar">
            <a href="/ethiotrip1/ethiotrip/public/admin/dashboard" class="logo">Ethio<span>Trip</span> Admin</a>
            <div class="nav">
                <a href="/ethiotrip1/ethiotrip/public/admin/bookings">Bookings</a>
                <a href="/ethiotrip1/ethiotrip/public/admin/packages">Packages</a>
                <a href="/ethiotrip1/ethiotrip/public/admin/destinations">Destinations</a>
                <a href="/ethiotrip1/ethiotrip/public/logout">Logout</a>
            </div>
        </div>
        <div class="header"><h1>Booking #<?php echo $booking['id']; ?></h1></div>
        <div class="card">
            <?php foreach ($booking as $key => $value): ?>
                <div class="row"><strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong> <?php echo htmlspecialchars($value ?? 'N/A'); ?></div>
            <?php endforeach; ?>
            <a href="/ethiotrip1/ethiotrip/public/admin/bookings" class="button">Back</a>
        </div>
    </div>
</body>
</html>