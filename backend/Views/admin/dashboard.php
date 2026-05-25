<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require __DIR__ . '/../partials/admin_head.php'; ?>
    <style>
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            border-left: 4px solid #d4af37;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { font-size: 0.85rem; text-transform: uppercase; color: #636e72; margin-bottom: 12px; }
        .stat-number { font-size: 2rem; font-weight: 700; color: #2d3436; }
        .stat-icon { float: right; font-size: 2rem; color: #d4af37; opacity: 0.5; }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin: 30px 0;
        }
        .quick-btn {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 16px;
            text-decoration: none;
            color: #2d3436;
            transition: 0.3s;
            border: 1px solid #eee;
        }
        .quick-btn:hover { background: #d4af37; color: white; transform: translateY(-3px); }
        .quick-btn i { font-size: 1.8rem; margin-bottom: 10px; display: block; }
        .quick-btn span { font-weight: 600; }
        .section-title { font-size: 1.2rem; margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid #d4af37; display: inline-block; }
        .recent-table { width: 100%; overflow-x: auto; }
        .recent-table table { width: 100%; border-collapse: collapse; }
        .recent-table th, .recent-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .recent-table th { background: #f8f9fa; font-weight: 600; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-completed { background: #d4edda; color: #155724; }
        .btn-view { background: #3498db; color: white; padding: 4px 10px; border-radius: 4px; text-decoration: none; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="page">
        <?php require __DIR__ . '/../partials/admin_navbar.php'; ?>

        <div class="header">
            <div>
                <h1>Admin Dashboard</h1>
                <p>Welcome back! Here's what's happening with your business today.</p>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card"><i class="fas fa-shopping-cart stat-icon"></i><h3>Total Bookings</h3><div class="stat-number"><?php echo number_format($totalBookings ?? 0); ?></div></div>
            <div class="stat-card"><i class="fas fa-clock stat-icon"></i><h3>Pending Approvals</h3><div class="stat-number"><?php echo number_format($pendingApprovals ?? 0); ?></div></div>
            <div class="stat-card"><i class="fas fa-money-bill-wave stat-icon"></i><h3>Pending Payments</h3><div class="stat-number"><?php echo number_format($pendingPayments ?? 0); ?></div></div>
            <div class="stat-card"><i class="fas fa-users stat-icon"></i><h3>Total Users</h3><div class="stat-number"><?php echo number_format($totalUsers ?? 0); ?></div></div>
            <div class="stat-card"><i class="fas fa-dollar-sign stat-icon"></i><h3>Total Revenue</h3><div class="stat-number">$<?php echo number_format($totalRevenue ?? 0, 2); ?></div></div>
            <div class="stat-card"><i class="fas fa-map-marker-alt stat-icon"></i><h3>Destinations</h3><div class="stat-number"><?php echo number_format($totalDestinations ?? 0); ?></div></div>
            <div class="stat-card"><i class="fas fa-box stat-icon"></i><h3>Packages</h3><div class="stat-number"><?php echo number_format($totalPackages ?? 0); ?></div></div>
        </div>

        <h3 class="section-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
        <div class="quick-actions">
            <a href="/ethiotrip1/ethiotrip/public/admin/bookings?filter=payment_pending" class="quick-btn"><i class="fas fa-credit-card"></i><span>Review Payments</span></a>
            <a href="/ethiotrip1/ethiotrip/public/admin/packages/create" class="quick-btn"><i class="fas fa-plus-circle"></i><span>Add Package</span></a>
            <a href="/ethiotrip1/ethiotrip/public/admin/destinations/create" class="quick-btn"><i class="fas fa-plus-circle"></i><span>Add Destination</span></a>
            <a href="/ethiotrip1/ethiotrip/public/admin/discounts" class="quick-btn"><i class="fas fa-percent"></i><span>Manage Discounts</span></a>
        </div>

        <h3 class="section-title"><i class="fas fa-recent"></i> Recent Bookings</h3>
        <div class="card">
            <div class="recent-table table-wrap admin-table-wrap">
                <?php if (!empty($recentBookings) && count($recentBookings) > 0): ?>
                    <table>
                        <thead><tr><th>ID</th><th>Customer</th><th>Package</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><?php echo htmlspecialchars($booking['user_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($booking['package_name'] ?? 'N/A'); ?></td>
                                    <td>$<?php echo number_format($booking['final_amount'], 2); ?></td>
                                    <td><span class="status-badge status-<?php echo $booking['payment_status']; ?>"><?php echo ucfirst($booking['payment_status'] ?? 'pending'); ?></span></td>
                                    <td><a href="/ethiotrip1/ethiotrip/public/admin/bookings/view?id=<?php echo $booking['id']; ?>" class="btn-view">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; padding: 40px;">No bookings found.</p>
                <?php endif; ?>
            </div>
        </div>

        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
        </footer>
    </div>
    <?php require __DIR__ . '/../partials/admin_footer_scripts.php'; ?>
</body>
</html>