<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | EthioTrip Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require __DIR__ . '/../partials/admin_head.php'; ?>
    <style>
        .button-save { background: #27ae60; color: #fff; box-shadow: none; }
        .button-save:hover { background: #219a52; }
        .button-cancel { background: #6c757d; color: #fff; box-shadow: none; }
        .button-cancel:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="page">
        <?php require __DIR__ . '/../partials/admin_navbar.php'; ?>

        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
            </div>
        </div>

        <div class="card">
            <div class="form-container">
                <form method="POST" action="<?php echo htmlspecialchars($formAction); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo Backend\Core\Session::csrfToken(); ?>">
                    
                    <div class="form-group">
                        <label>Destination *</label>
                        <select name="destination_id" required>
                            <option value="">Select destination</option>
                            <?php foreach (($destinations ?? []) as $dest): ?>
                                <option value="<?php echo (int) $dest['id']; ?>" <?php echo ((int)($form['destination_id'] ?? 0) === (int)$dest['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dest['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Package Name *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($form['name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Price (USD) *</label>
                        <input type="number" step="0.01" min="0.01" name="price" value="<?php echo htmlspecialchars($form['price'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Duration</label>
                        <input type="text" name="duration" value="<?php echo htmlspecialchars($form['duration'] ?? ''); ?>" placeholder="e.g., 3 Days / 2 Nights">
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">Select Category</option>
                            <option value="cultural" <?php echo (($form['category'] ?? '') === 'cultural') ? 'selected' : ''; ?>>Cultural</option>
                            <option value="adventure" <?php echo (($form['category'] ?? '') === 'adventure') ? 'selected' : ''; ?>>Adventure</option>
                            <option value="luxury" <?php echo (($form['category'] ?? '') === 'luxury') ? 'selected' : ''; ?>>Luxury</option>
                            <option value="nature" <?php echo (($form['category'] ?? '') === 'nature') ? 'selected' : ''; ?>>Nature</option>
                            <option value="short_escape" <?php echo (($form['category'] ?? '') === 'short_escape') ? 'selected' : ''; ?>>Short Escape</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description"><?php echo htmlspecialchars($form['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Features</label>
                        <textarea name="features" placeholder="Enter one feature per line..."><?php echo htmlspecialchars($form['features'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active">
                            <option value="1" <?php echo (!empty($form['is_active']) || !isset($form['is_active'])) ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo (isset($form['is_active']) && !$form['is_active']) ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button-save"><i class="fas fa-save"></i> <?php echo htmlspecialchars($buttonText); ?></button>
                        <a href="/ethiotrip1/ethiotrip/public/admin/packages" class="button-cancel"><i class="fas fa-times"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> EthioTrip Ethiopia.</div>
        </footer>
    </div>
    <?php require __DIR__ . '/../partials/admin_footer_scripts.php'; ?>
</body>
</html>