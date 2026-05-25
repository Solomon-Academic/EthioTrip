<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Bookings | EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ethiotrip1/ethiotrip/public/css/backend.css">
</head>
<body>
    <div class="page">
        <div class="navbar">
            <a href="/ethiotrip1/ethiotrip/public/dashboard" class="logo">Ethio<span class="accent-text">Trip</span> Admin</a>
            <div class="nav">
                <a href="/ethiotrip1/ethiotrip/public/admin/bookings">Payments</a>
                <a href="/ethiotrip1/ethiotrip/public/admin/packages">Packages</a>
                <a href="/ethiotrip1/ethiotrip/public/admin/destinations">Destinations</a>
                <a href="/ethiotrip1/ethiotrip/public/admin/discounts">Discounts</a>
                <a href="/ethiotrip1/ethiotrip/public/logout">Logout</a>
            </div>
        </div>

        <div class="header">
            <div>
                <h1>Payment Review</h1>
                <p style="color:#636e72; margin-top:8px;">Review bookings with pending payments and approve them after confirming the payment details.</p>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="cards">
            <div class="card">
                <h3>Total Bookings</h3>
                <span><?php echo intval($stats['total'] ?? 0); ?></span>
            </div>
            <div class="card">
                <h3>Pending Review</h3>
                <span><?php echo intval($stats['pending'] ?? 0); ?></span>
            </div>
            <div class="card">
                <h3>Approved</h3>
                <span><?php echo intval($stats['approved'] ?? 0); ?></span>
            </div>
            <div class="card">
                <h3>Revenue</h3>
                <span>$<?php echo number_format(floatval($stats['total_revenue'] ?? 0), 2); ?></span>
            </div>
        </div>

        <div class="card">
            <form method="GET" action="/ethiotrip1/ethiotrip/public/admin/bookings" class="row">
                <div class="form-group">
                    <label>Filter</label>
                    <select name="filter">
                        <option value="payment_pending" <?php echo ($currentFilter ?? '') === 'payment_pending' ? 'selected' : ''; ?>>Pending payments</option>
                        <option value="pending" <?php echo ($currentFilter ?? '') === 'pending' ? 'selected' : ''; ?>>Pending bookings</option>
                        <option value="approved" <?php echo ($currentFilter ?? '') === 'approved' ? 'selected' : ''; ?>>Approved bookings</option>
                        <option value="rejected" <?php echo ($currentFilter ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected bookings</option>
                        <option value="all" <?php echo ($currentFilter ?? '') === 'all' ? 'selected' : ''; ?>>All bookings</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Search</label>
                    <input type="search" name="search" value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>" placeholder="Name, package, or transaction ID">
                </div>
                <div class="form-group" style="align-self:end;">
                    <button class="button" type="submit">Apply</button>
                </div>
            </form>
        </div>

        <div class="card">
            <?php if ($bookings instanceof \mysqli_result && $bookings->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Trip</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Booking</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo intval($booking['id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($booking['user_name'] ?? 'Unknown'); ?></strong><br>
                                    <span style="color:#636e72;"><?php echo htmlspecialchars($booking['user_email'] ?? ''); ?></span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($booking['package_name'] ?? ''); ?><br>
                                    <span style="color:#636e72;">
                                        <?php echo htmlspecialchars($booking['destination'] ?? ''); ?>,
                                        <?php echo htmlspecialchars($booking['start_date']); ?> to <?php echo htmlspecialchars($booking['end_date']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format(floatval($booking['final_amount']), 2); ?></td>
                                <td>
                                    <span class="status status-<?php echo htmlspecialchars($booking['payment_status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($booking['payment_status'])); ?>
                                    </span><br>
                                    <span style="color:#636e72;"><?php echo htmlspecialchars($booking['payment_method'] ?? ''); ?></span>
                                </td>
                                <td>
                                    <span class="status status-<?php echo htmlspecialchars($booking['admin_approval_status'] ?? 'pending'); ?>">
                                        <?php echo ucfirst(htmlspecialchars($booking['admin_approval_status'] ?? 'pending')); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <?php if (($booking['payment_status'] ?? '') === 'pending'): ?>
                                        <form method="POST" action="/ethiotrip1/ethiotrip/public/admin/bookings" style="display:inline;">
                                            <?php echo $this->csrfField(); ?>
                                            <input type="hidden" name="booking_id" value="<?php echo intval($booking['id']); ?>">
                                            <input type="hidden" name="action" value="approve_payment">
                                            <button type="submit">Approve Payment</button>
                                        </form>
                                        <form method="POST" action="/ethiotrip1/ethiotrip/public/admin/bookings" style="display:inline;" onsubmit="return confirm('Mark this payment as failed?');">
                                            <?php echo $this->csrfField(); ?>
                                            <input type="hidden" name="booking_id" value="<?php echo intval($booking['id']); ?>">
                                            <input type="hidden" name="action" value="fail_payment">
                                            <button type="submit" class="button-danger">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:#636e72;">Reviewed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No bookings match this filter.</p>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <div class="footer-inner">&copy; <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
        </footer>
    </div>
</body>
</html>
