<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Packages | EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ethiotrip1/ethiotrip/public/css/backend.css">
</head>
<body>
    <div class="page">
        <div class="navbar">
            <a href="/ethiotrip1/ethiotrip/public/dashboard" class="logo">Ethio<span class="accent-text">Trip</span> Admin</a>
            <div class="nav">
                <a href="/ethiotrip1/ethiotrip/public/admin/bookings">Payments</a>
                <a href="/ethiotrip1/ethiotrip/public/admin/destinations">Destinations</a>
                <a href="/ethiotrip1/ethiotrip/public/admin/discounts">Discounts</a>
                <a href="/ethiotrip1/ethiotrip/public/logout">Logout</a>
            </div>
        </div>

        <div class="header">
            <div>
                <h1>Package Management</h1>
                <p style="color:#636e72; margin-top:8px;">Create packages, update prices, and control package availability.</p>
            </div>
            <a class="button" href="/ethiotrip1/ethiotrip/public/admin/packages/create">Add New Package</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($packages instanceof \mysqli_result && $packages->num_rows > 0): ?>
                        <?php while ($package = $packages->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($package['name']); ?></td>
                                <td>$<?php echo number_format($package['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($package['duration'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($package['category'] ?? ''); ?></td>
                                <td>
                                    <span class="status <?php echo $package['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $package['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="/ethiotrip1/ethiotrip/public/admin/packages/edit?id=<?php echo $package['id']; ?>">Edit</a>
                                    <form method="POST" action="/ethiotrip1/ethiotrip/public/admin/packages/delete?id=<?php echo $package['id']; ?>" style="display:inline;" onsubmit="return confirm('Delete this package?');">
                                        <?php echo $this->csrfField(); ?>
                                        <button type="submit" class="button-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; color:#777;">No packages available yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
        </footer>
    </div>
</body>
</html>
