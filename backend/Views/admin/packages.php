<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages | EthioTrip Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require __DIR__ . '/../partials/admin_head.php'; ?>
    <style>
        .success-message { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
        .btn-add { background: #27ae60; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; }
        .btn-add:hover { background: #219a52; }
        .btn-edit { background: #3498db; color: white; padding: 5px 12px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; display: inline-block; }
        .btn-edit:hover { background: #2980b9; }
        .btn-delete { background: #e74c3c; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; }
        .btn-delete:hover { background: #c0392b; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .status-active { background: #d4edda; color: #155724; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
        .status-inactive { background: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; display: inline-block; }
    </style>
</head>
<body>
    <div class="page">
        <?php require __DIR__ . '/../partials/admin_navbar.php'; ?>

        <div class="header">
            <div>
                <h1>Package Management</h1>
                <p>Create, edit, or delete travel packages.</p>
            </div>
            <a class="btn-add" href="/ethiotrip1/ethiotrip/public/admin/packages/create"><i class="fas fa-plus"></i> Add New Package</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <?php if ($packages instanceof \mysqli_result && $packages->num_rows > 0): ?>
                <div class="table-wrap admin-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Destination</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($package = $packages->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $package['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($package['name']); ?></strong></td>
                                    <td>$<?php echo number_format($package['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($package['duration'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($package['destination_name'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($package['category'] ?? 'Uncategorized'); ?></td>
                                    <td>
                                        <span class="status-<?php echo $package['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $package['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="/ethiotrip1/ethiotrip/public/admin/packages/edit?id=<?php echo $package['id']; ?>" class="btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST" action="/ethiotrip1/ethiotrip/public/admin/packages/delete?id=<?php echo $package['id']; ?>" style="display:inline;" onsubmit="return confirm('Delete this package permanently?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo Backend\Core\Session::csrfToken(); ?>">
                                            <button type="submit" class="btn-delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align:center; padding:40px;">No packages yet. <a href="/ethiotrip1/ethiotrip/public/admin/packages/create">Add your first package</a></p>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> EthioTrip Ethiopia.</div>
        </footer>
    </div>
    <?php require __DIR__ . '/../partials/admin_footer_scripts.php'; ?>
</body>
</html>