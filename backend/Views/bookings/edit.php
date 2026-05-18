<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking - EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ethiotrip1/ethiotrip/public/css/backend.css">
</head>
<body>
    <div class="page">
        <div class="navbar">
            <a href="/ethiotrip1/ethiotrip/public/dashboard" class="logo">Ethio<span style="color:#d4af37;">Trip</span></a>
            <div>
                <a href="/ethiotrip1/ethiotrip/public/bookings">My Bookings</a>
                <a href="/ethiotrip1/ethiotrip/public/logout">Logout</a>
            </div>
        </div>

        <div class="card">
            <h1>Edit Booking #<?php echo $booking['id']; ?></h1>
            <?php if (!empty($errors['general'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['general']); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php echo $this->csrfField(); ?>
                <div class="form-group">
                    <label>Package</label>
                    <input type="text" readonly value="<?php echo htmlspecialchars($booking['package_name']); ?>">
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($form['start_date']); ?>" required>
                    <?php if (!empty($errors['start_date'])): ?><div class="error"><?php echo htmlspecialchars($errors['start_date']); ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($form['end_date']); ?>" required>
                    <?php if (!empty($errors['end_date'])): ?><div class="error"><?php echo htmlspecialchars($errors['end_date']); ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Travelers</label>
                    <input type="number" name="travelers" min="1" max="20" value="<?php echo htmlspecialchars($form['travelers']); ?>" required>
                    <?php if (!empty($errors['travelers'])): ?><div class="error"><?php echo htmlspecialchars($errors['travelers']); ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Special Requests</label>
                    <textarea name="special_requests" rows="4"><?php echo htmlspecialchars($form['special_requests']); ?></textarea>
                </div>
                <button class="button" type="submit">Update Booking</button>
                <a class="button button-secondary" href="/ethiotrip1/ethiotrip/public/bookings">Cancel</a>
            </form>
        </div>
        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
        </footer>
    </div>
</body>
</html>
