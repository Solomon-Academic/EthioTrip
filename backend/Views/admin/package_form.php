<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | EthioTrip Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ethiotrip1/ethiotrip/public/css/backend.css">
</head>
<body>
    <div class="page">
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                <p style="color:#636e72; margin-top:8px;">Manage package prices, duration, features, and availability.</p>
            </div>
            <a class="button" href="/ethiotrip1/ethiotrip/public/admin/packages">Back to Packages</a>
        </div>

        <div class="card">
            <?php if (!empty($errors['general'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['general']); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($formAction); ?>">
                <?php echo $this->csrfField(); ?>
                <div class="grid-cols">
                    <div class="form-group">
                        <label>Package Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($form['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Price (USD)</label>
                        <input type="number" step="0.01" min="0.01" name="price" value="<?php echo htmlspecialchars($form['price'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Duration</label>
                        <input type="text" name="duration" value="<?php echo htmlspecialchars($form['duration'] ?? ''); ?>" placeholder="3 Days / 2 Nights">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" name="category" value="<?php echo htmlspecialchars($form['category'] ?? ''); ?>" placeholder="cultural">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?php echo htmlspecialchars($form['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Features</label>
                    <textarea name="features" placeholder="Enter one feature per line, or JSON if preferred."><?php echo htmlspecialchars($form['features'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Active</label>
                    <select name="is_active">
                        <option value="1" <?php echo (!empty($form['is_active']) || !isset($form['is_active'])) ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?php echo (isset($form['is_active']) && !$form['is_active']) ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>

                <button class="button" type="submit"><?php echo htmlspecialchars($buttonText); ?></button>
            </form>
        </div>
        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
        </footer>
    </div>
</body>
</html>
