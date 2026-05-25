<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinations | EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/backend.css">
</head>
<body>
    <div class="page">
        <div class="hero">
            <div>
                <h1>Explore Ethiopia's Most Iconic Destinations</h1>
                <p>Find the perfect journey for your group with curated travel characters, premier lodges, and immersive cultural experiences.</p>
            </div>
            <a class="button" href="home.html">Back to Home</a>
        </div>

        <div class="grid">
            <?php if ($destinations instanceof \mysqli_result && $destinations->num_rows > 0): ?>
                <?php while ($destination = $destinations->fetch_assoc()): ?>
                    <div class="card">
                        <?php if (!empty($destination['image_path'])): ?>
                            <img src="/<?php echo htmlspecialchars($destination['image_path']); ?>" alt="<?php echo htmlspecialchars($destination['name']); ?>">
                        <?php else: ?>
                            <img src="/IP2_travel/ethiotrip/public/images/dest_images/upscaled_lalibela.jpg" alt="<?php echo htmlspecialchars($destination['name']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="meta">
                                <span class="tag"><?php echo htmlspecialchars($destination['location']); ?></span>
                                <span class="tag">Best time: <?php echo htmlspecialchars($destination['best_time'] ?: 'Any season'); ?></span>
                                <span class="tag">From $<?php echo number_format($destination['price'], 2); ?></span>
                            </div>
                            <h2><?php echo htmlspecialchars($destination['name']); ?></h2>
                            <p><?php echo nl2br(htmlspecialchars($destination['description'] ?: 'Discover the destination highlights, culture, and local experiences.')); ?></p>
                            <a class="button" href="/destination.php?id=<?php echo $destination['id']; ?>">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column:1/-1; padding:40px; text-align:center; color:#555; background:white; border-radius:24px; box-shadow:0 20px 50px rgba(0,0,0,.05);">
                    No destinations are available yet. Please check back soon.
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">© <?php echo date('Y'); ?> EthioTrip Ethiopia. Professional tours designed to delight every traveler.</div>
    </div>
</body>
</html>
