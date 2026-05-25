<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Discounts | EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require __DIR__ . '/../partials/admin_head.php'; ?>
</head>
<body>
    <div class="page">
        <?php require __DIR__ . '/../partials/admin_navbar.php'; ?>

        <div class="header">
            <h1>Discount Tier Management</h1>
        </div>

        <div class="card">
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <div class="table-wrap admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tier Name</th>
                        <th>Min Trips</th>
                        <th>Max Trips</th>
                        <th>Discount %</th>
                        <th>Status</th>
                        <th>Save</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tiers instanceof \mysqli_result): ?>
                    <?php while ($tier = $tiers->fetch_assoc()): ?>
                        <tr>
                            <form method="POST">
                                <?php echo $this->csrfField(); ?>
                                <td><input type="text" name="tier_name" value="<?php echo htmlspecialchars($tier['tier_name']); ?>" required></td>
                                <td><input type="number" name="min_trips" value="<?php echo htmlspecialchars($tier['min_trips']); ?>" required></td>
                                <td><input type="number" name="max_trips" value="<?php echo htmlspecialchars($tier['max_trips'] ?? ''); ?>"></td>
                                <td><input type="number" step="0.5" name="discount_percent" value="<?php echo htmlspecialchars($tier['discount_percent']); ?>" required></td>
                                <td>
                                    <select name="is_active">
                                        <option value="1" <?php echo $tier['is_active'] ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?php echo !$tier['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="id" value="<?php echo $tier['id']; ?>">
                                    <button type="submit" name="update_tier" class="button">Save</button>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

        <div class="card">
            <h2>Add New Tier</h2>
            <form method="POST">
                <?php echo $this->csrfField(); ?>
                <div class="grid-cols">
                    <div class="form-group"><label>Tier Name</label><input type="text" name="tier_name" required></div>
                    <div class="form-group"><label>Min Trips</label><input type="number" name="min_trips" required></div>
                    <div class="form-group"><label>Max Trips</label><input type="number" name="max_trips"></div>
                    <div class="form-group"><label>Discount %</label><input type="number" step="0.5" name="discount_percent" required></div>
                </div>
                <button class="button mt-18" type="submit" name="add_tier">Add Tier</button>
            </form>
        </div>

        <div class="card">
            <h2>Quick Adjust</h2>
            <p>Adjust active discounts by a fixed amount.</p>
            <div class="grid-cols-sm">
                <form method="POST"><?php echo $this->csrfField(); ?><input type="hidden" name="adjustment" value="5"><button class="button" type="submit">+5%</button></form>
                <form method="POST"><?php echo $this->csrfField(); ?><input type="hidden" name="adjustment" value="-5"><button class="button" type="submit">-5%</button></form>
                <form method="POST"><?php echo $this->csrfField(); ?><input type="hidden" name="adjustment" value="10"><button class="button" type="submit">+10%</button></form>
                <form method="POST"><?php echo $this->csrfField(); ?><input type="hidden" name="adjustment" value="-10"><button class="button" type="submit">-10%</button></form>
            </div>
        </div>

        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia.</div>
        </footer>
    </div>
    <?php require __DIR__ . '/../partials/admin_footer_scripts.php'; ?>
</body>
</html>
