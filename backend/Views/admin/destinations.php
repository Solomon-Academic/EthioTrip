<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Destinations | EthioTrip</title>
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
                <a href="/ethiotrip1/ethiotrip/public/admin/discounts">Discounts</a>
                <a href="/ethiotrip1/ethiotrip/public/logout">Logout</a>
            </div>
        </div>

        <div class="header">
            <div>
                <h1>Destination Management</h1>
                <p style="color:#636e72; margin-top:8px;">Add, edit or remove destination guides and destination files from the admin dashboard.</p>
            </div>
            <a class="button" href="/ethiotrip1/ethiotrip/public/admin/destinations/create">Add New Destination</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Best Time</th>
                        <th>Status</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($destinations instanceof \mysqli_result && $destinations->num_rows > 0): ?>
                        <?php while ($destination = $destinations->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($destination['name']); ?></td>
                                <td><?php echo htmlspecialchars($destination['location']); ?></td>
                                <td>$<?php echo number_format($destination['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($destination['best_time']); ?></td>
                                <td>
                                    <span class="status <?php echo $destination['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $destination['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($destination['image_path'])): ?>
                                        <img src="/<?php echo htmlspecialchars($destination['image_path']); ?>" alt="<?php echo htmlspecialchars($destination['name']); ?>">
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="/ethiotrip1/ethiotrip/public/admin/destinations/edit?id=<?php echo $destination['id']; ?>">Edit</a>
                                    <form method="POST" action="/ethiotrip1/ethiotrip/public/admin/destinations/delete?id=<?php echo $destination['id']; ?>" onsubmit="return confirm('Delete this destination permanently?');" style="display:inline;">
                                        <?php echo $this->csrfField(); ?>
                                        <button type="submit" class="button-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center; color:#777;">No destinations available yet.</td></tr>
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
