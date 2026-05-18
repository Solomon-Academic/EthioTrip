<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ethiotrip1/ethiotrip/public/css/backend.css">
</head>
<body>
    <div class="card">
        <h1>Sign In</h1>
        <?php if (!empty($errors['general'])): ?>
            <div class="message"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <?php echo $this->csrfField(); ?>
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            <?php if (!empty($errors['email'])): ?><div class="error"><?php echo htmlspecialchars($errors['email']); ?></div><?php endif; ?>
            <label>Password</label>
            <input type="password" name="password" required>
            <?php if (!empty($errors['password'])): ?><div class="error"><?php echo htmlspecialchars($errors['password']); ?></div><?php endif; ?>
            <button class="btn" type="submit">Login</button>
        </form>
        <a class="link" href="/ethiotrip1/ethiotrip/public/register">Don't have an account? Register</a>
    </div>
    <footer class="footer">
        <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
    </footer>
</body>
</html>
