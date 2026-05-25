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
        .button-cancel { background: #6c757d; color: #fff; box-shadow: none; }
        .image-preview { max-width: 100%; width: 150px; margin-top: 10px; border-radius: 8px; }
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
                <form method="POST" action="<?php echo htmlspecialchars($formAction); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo Backend\Core\Session::csrfToken(); ?>">
                    
                    <div class="form-group">
                        <label>Destination Name *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($form['name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Location / Region *</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($form['location'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Price (USD) *</label>
                        <input type="number" step="0.01" min="0" name="price" value="<?php echo htmlspecialchars($form['price'] ?? 0); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Best Time to Visit</label>
                        <input type="text" name="best_time" value="<?php echo htmlspecialchars($form['best_time'] ?? ''); ?>" placeholder="e.g., Oct - Mar">
                    </div>

                    <div class="form-group">
                        <label>Short Description (card preview)</label>
                        <textarea name="short_description" rows="2"><?php echo htmlspecialchars($form['short_description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Full Description</label>
                        <textarea name="description"><?php echo htmlspecialchars($form['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Travel Guide</label>
                        <textarea name="travel_guide" rows="5" placeholder="Practical tips, culture, what to pack..."><?php echo htmlspecialchars($form['travel_guide'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Activities</label>
                        <input type="text" name="activities" value="<?php echo htmlspecialchars($form['activities'] ?? ''); ?>" placeholder="e.g. Church tours, hiking">
                    </div>

                    <div class="form-group">
                        <label>Highlights (Title|Description per line)</label>
                        <textarea name="highlights_text" rows="6" placeholder="Bete Medhane Alem|Largest rock-hewn church..."><?php echo htmlspecialchars($form['highlights_text'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Related Attractions (Name|Description per line)</label>
                        <textarea name="attractions_text" rows="4" placeholder="Asheten Maryam|Panoramic viewpoint..."><?php echo htmlspecialchars($form['attractions_text'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Destination Image</label>
                        <input type="file" name="image" accept="image/*">
                        <?php if (!empty($form['image_path'])): ?>
                            <img src="/<?php echo $form['image_path']; ?>" class="image-preview">
                        <?php endif; ?>
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
                        <a href="/ethiotrip1/ethiotrip/public/admin/destinations" class="button-cancel"><i class="fas fa-times"></i> Cancel</a>
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