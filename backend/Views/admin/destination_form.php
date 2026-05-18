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
                <p style="color:#636e72; margin-top:8px;">Upload a destination photo or file and manage each destination's profile.</p>
            </div>
            <a class="button" href="/ethiotrip1/ethiotrip/public/admin/destinations">Back to Destinations</a>
        </div>

        <div class="card">
            <?php if (!empty($errors['general'])): ?>
                <div class="message"><?php echo htmlspecialchars($errors['general']); ?></div>
            <?php endif; ?>
            <form method="POST" action="<?php echo htmlspecialchars($formAction); ?>" enctype="multipart/form-data">
                <?php echo $this->csrfField(); ?>
                <div class="row">
                    <div class="form-group">
                        <label>Destination Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($form['name'] ?? ''); ?>" required>
                        <?php if (!empty($errors['name'])): ?><div class="error"><?php echo htmlspecialchars($errors['name']); ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Location / Region</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($form['location'] ?? ''); ?>" required>
                        <?php if (!empty($errors['location'])): ?><div class="error"><?php echo htmlspecialchars($errors['location']); ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label>Best Time to Visit</label>
                        <input type="text" name="best_time" value="<?php echo htmlspecialchars($form['best_time'] ?? ''); ?>" placeholder="e.g. Oct - Mar">
                    </div>
                    <div class="form-group">
                        <label>Starting Price (USD)</label>
                        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($form['price'] ?? ''); ?>" required>
                        <?php if (!empty($errors['price'])): ?><div class="error"><?php echo htmlspecialchars($errors['price']); ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="row full">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description"><?php echo htmlspecialchars($form['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="row full">
                    <div class="form-group">
                        <label>Churches or Highlights</label>
                        <textarea name="churches" placeholder="Enter one church or highlight per line."><?php echo htmlspecialchars($form['churches'] ?? ''); ?></textarea>
                        <small style="color:#666;">For Lalibela, add churches like Bete Medhane Alem, Bete Maryam, Bete Gabriel-Rufael.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label>Destination Image</label>
                        <input type="file" name="image" accept="image/*">
                        <?php if (!empty($errors['image'])): ?><div class="error"><?php echo htmlspecialchars($errors['image']); ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Optional Destination File</label>
                        <input type="file" name="attachment" accept="application/pdf,image/*,application/zip">
                        <?php if (!empty($errors['attachment'])): ?><div class="error"><?php echo htmlspecialchars($errors['attachment']); ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label>Active</label>
                        <select name="is_active">
                            <option value="1" <?php echo (!empty($form['is_active']) || !isset($form['is_active'])) ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo (isset($form['is_active']) && !$form['is_active']) ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
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
