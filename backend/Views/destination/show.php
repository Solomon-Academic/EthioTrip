<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($destination['name']); ?> | EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/backend.css">
</head>
<body>
    <div class="page">
        <div class="breadcrumbs"><a href="/destinations.php">Destinations</a> → <?php echo htmlspecialchars($destination['name']); ?></div>

        <div class="hero">
            <?php if (!empty($destination['image_path'])): ?>
                <img src="/<?php echo htmlspecialchars($destination['image_path']); ?>" alt="<?php echo htmlspecialchars($destination['name']); ?>">
            <?php else: ?>
                <img src="/IP2_travel/ethiotrip/public/images/dest_images/upscaled_lalibela.jpg" alt="<?php echo htmlspecialchars($destination['name']); ?>">
            <?php endif; ?>

            <div class="hero-content">
                <h1><?php echo htmlspecialchars($destination['name']); ?></h1>
                <div class="meta">
                    <span class="tag"><?php echo htmlspecialchars($destination['location']); ?></span>
                    <span class="tag">Best time: <?php echo htmlspecialchars($destination['best_time'] ?: 'All year'); ?></span>
                    <span class="tag">From $<?php echo number_format($destination['price'], 2); ?></span>
                </div>
                <p><?php echo nl2br(htmlspecialchars($destination['description'] ?: 'Experience an unforgettable Ethiopian destination curated for modern travelers.')); ?></p>
                <div class="download-links">
                    <?php if (!empty($destination['attachment_path'])): ?>
                        <a href="/<?php echo htmlspecialchars($destination['attachment_path']); ?>" target="_blank">Download Destination File</a>
                    <?php endif; ?>
                    <a class="button" href="/packages.html">View Packages</a>
                </div>
            </div>
        </div>

        <?php if (!empty($destination['churches'])): ?>
            <div class="section">
                <h2><?php echo htmlspecialchars($destination['name']); ?> Highlights</h2>
                <div class="churches-grid">
                    <?php foreach ((array) preg_split('/\r?\n/', trim($destination['churches'])) as $church): ?>
                        <?php if (trim($church) === '') continue; ?>
                        <div class="church-card"><?php echo htmlspecialchars($church); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($destination['churches']) && strtolower(trim($destination['name'])) === 'lalibela'): ?>
            <div class="section">
                <h2>Churches of Lalibela</h2>
                <p>The rock-hewn churches in Lalibela are world-famous for their architectural courage and spiritual presence. These sacred monuments include:</p>
                <div class="churches-grid">
                    <div class="church-card">Bete Medhane Alem</div>
                    <div class="church-card">Bete Maryam</div>
                    <div class="church-card">Bete Gabriel-Rufael</div>
                    <div class="church-card">Bete Golgotha Mikael</div>
                    <div class="church-card">Bete Amanuel</div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
