<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ethiotrip1/ethiotrip/public/css/backend.css">
</head>
<body>
    <div class="page">
        <div class="navbar">
            <a href="/ethiotrip1/ethiotrip/public/dashboard" class="logo">Ethio<span>Trip</span></a>
            <div class="nav">
                <a href="/ethiotrip1/ethiotrip/public/bookings">My Bookings</a>
                <a href="/ethiotrip1/ethiotrip/public/logout">Logout</a>
            </div>
        </div>

        <div class="hero">
            <h1>Welcome, <?php echo htmlspecialchars($user['name'] ?? 'Traveler'); ?>!</h1>
            <p>Track your bookings, loyalty rewards, and travel progress from one place.</p>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Total Bookings</h3>
                <span><?php echo $totalBookings; ?></span>
            </div>
            <div class="card">
                <h3>Total Spent</h3>
                <span>$<?php echo number_format($totalSpent, 2); ?></span>
            </div>
            <div class="card">
                <h3>Trips Completed</h3>
                <span><?php echo $trips; ?></span>
            </div>
            <div class="card discount">
                <h3>Loyalty Discount</h3>
                <span><?php echo $discountPercent; ?>%</span>
            </div>
        </div>

        <?php if (!empty($nextTier)): ?>
            <div class="card" style="margin-top:24px;">
                <h3>Next Tier Progress</h3>
                <p><?php echo max(0, intval($nextTier['min_trips']) - $trips); ?> more trip(s) needed for <?php echo htmlspecialchars($nextTier['tier_name']); ?>.</p>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a class="action" href="/ethiotrip1/ethiotrip/public/bookings">View My Bookings</a>
            <a class="action" href="/ethiotrip1/ethiotrip/public/bookings/create">Create Booking</a>
            <?php if (($user['role'] ?? '') === 'admin'): ?>
                <a class="action" href="/ethiotrip1/ethiotrip/public/admin/destinations">Manage Destinations</a>
                <a class="action" href="/ethiotrip1/ethiotrip/public/admin/packages">Manage Packages & Prices</a>
                <a class="action" href="/ethiotrip1/ethiotrip/public/admin/discounts">Manage Discounts</a>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
        </footer>
    </div>
</body>
</html>
