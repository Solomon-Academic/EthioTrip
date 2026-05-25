<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Booking - EthioTrip</title>
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

        <div class="header">
            <div>
                <h1>Create Booking</h1>
                <p>Choose your trip details once, then continue to payment.</p>
            </div>
            <a class="button button-secondary" href="/ethiotrip1/ethiotrip/public/bookings">Back to My Bookings</a>
        </div>

        <div class="card">
            <div class="highlight">
                Booking for <?php echo htmlspecialchars($user['name'] ?? 'Traveler'); ?>
                <?php if (!empty($user['email'])): ?>
                    (<?php echo htmlspecialchars($user['email']); ?>)
                <?php endif; ?>
            </div>
            <?php if (!empty($errors['general'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['general']); ?></div>
            <?php endif; ?>

            <?php if ($discountPercent > 0): ?>
                <div class="highlight">You qualify for <?php echo $discountPercent; ?>% loyalty discount.</div>
            <?php endif; ?>

            <form method="POST" action="" id="bookingCreateForm">
                <?php echo $this->csrfField(); ?>
                <div class="form-group">
                    <label>Select Destination</label>
                    <select name="destination_id" id="destinationSelect" required>
                        <option value="">Choose a destination first</option>
                        <?php while ($destination = $destinations->fetch_assoc()): ?>
                            <option value="<?php echo $destination['id']; ?>" data-name="<?php echo htmlspecialchars($destination['name']); ?>" <?php echo ($destination['id'] == ($form['destination_id'] ?? '')) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($destination['name']); ?> - <?php echo htmlspecialchars($destination['location'] ?? ''); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <?php if (!empty($errors['destination'])): ?><div class="error"><?php echo htmlspecialchars($errors['destination']); ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Select Package</label>
                    <select name="package_id" id="packageSelect" required>
                        <option value="">Choose a package</option>
                        <?php while ($package = $packages->fetch_assoc()): ?>
                            <option value="<?php echo $package['id']; ?>" data-name="<?php echo htmlspecialchars($package['name']); ?>" data-price="<?php echo htmlspecialchars($package['price']); ?>" <?php echo ($package['id'] == ($form['package_id'] ?? '')) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($package['name']); ?> - $<?php echo number_format($package['price'], 2); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <?php if (!empty($errors['package'])): ?><div class="error"><?php echo htmlspecialchars($errors['package']); ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" id="startDateField" value="<?php echo htmlspecialchars($form['start_date'] ?? ''); ?>" required>
                    <?php if (!empty($errors['start_date'])): ?><div class="error"><?php echo htmlspecialchars($errors['start_date']); ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" id="endDateField" value="<?php echo htmlspecialchars($form['end_date'] ?? ''); ?>" required>
                    <?php if (!empty($errors['end_date'])): ?><div class="error"><?php echo htmlspecialchars($errors['end_date']); ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Number of Travelers</label>
                    <input type="number" name="travelers" id="travelersField" min="1" max="20" value="<?php echo htmlspecialchars($form['travelers'] ?? 1); ?>" required>
                    <?php if (!empty($errors['travelers'])): ?><div class="error"><?php echo htmlspecialchars($errors['travelers']); ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Special Requests</label>
                    <textarea name="special_requests" id="specialRequestsField" rows="4"><?php echo htmlspecialchars($form['special_requests'] ?? ''); ?></textarea>
                </div>
                <button class="button" type="button" id="continueToPayment">Continue to Payment</button>
                <a class="button button-secondary" href="/ethiotrip1/ethiotrip/public/bookings">Cancel</a>
            </form>
        </div>
        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
        </footer>
    </div>
    <script>
        document.getElementById('continueToPayment').addEventListener('click', () => {
            const destination = document.getElementById('destinationSelect');
            const packageSelect = document.getElementById('packageSelect');
            const startDate = document.getElementById('startDateField').value;
            const endDate = document.getElementById('endDateField').value;
            const travelers = document.getElementById('travelersField').value || '1';
            const selectedDestination = destination.options[destination.selectedIndex];
            const selectedPackage = packageSelect.options[packageSelect.selectedIndex];
            const packagePrice = Number(selectedPackage?.dataset.price || 0);

            if (!destination.value || !packageSelect.value || !startDate || !endDate || packagePrice <= 0) {
                document.getElementById('bookingCreateForm').reportValidity();
                return;
            }

            localStorage.setItem('selectedDestinationName', selectedDestination.dataset.name || selectedDestination.textContent.trim());
            localStorage.setItem('selectedPackage', selectedPackage.dataset.name || selectedPackage.textContent.trim());
            localStorage.setItem('selectedPrice', packagePrice.toFixed(2));
            localStorage.setItem('pricePerDay', (packagePrice / 3).toFixed(2));
            localStorage.setItem('selectedStartDate', startDate);
            localStorage.setItem('selectedEndDate', endDate);
            localStorage.setItem('selectedTravelers', travelers);
            localStorage.setItem('specialRequests', document.getElementById('specialRequestsField').value || '');
            window.location.href = '/ethiotrip1/ethiotrip/public/payment';
        });
    </script>
</body>
</html>
