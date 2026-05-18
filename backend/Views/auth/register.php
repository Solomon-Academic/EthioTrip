<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ethiotrip1/ethiotrip/public/css/backend.css">
</head>
<body>
    <div class="card">
        <h1>Create Account</h1>
        <?php if (!empty($errors['general'])): ?>
            <div class="message"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <?php echo $this->csrfField(); ?>
            <label>Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($form['name'] ?? ''); ?>" required>
            <?php if (!empty($errors['name'])): ?><div class="error"><?php echo htmlspecialchars($errors['name']); ?></div><?php endif; ?>
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($form['email'] ?? ''); ?>" required>
            <?php if (!empty($errors['email'])): ?><div class="error"><?php echo htmlspecialchars($errors['email']); ?></div><?php endif; ?>
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($form['phone'] ?? ''); ?>" required>
            <?php if (!empty($errors['phone'])): ?><div class="error"><?php echo htmlspecialchars($errors['phone']); ?></div><?php endif; ?>
            <label>Password</label>
            <input type="password" name="password" required>
            <?php if (!empty($errors['password'])): ?><div class="error"><?php echo htmlspecialchars($errors['password']); ?></div><?php endif; ?>
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
            <?php if (!empty($errors['confirm_password'])): ?><div class="error"><?php echo htmlspecialchars($errors['confirm_password']); ?></div><?php endif; ?>
            <button class="btn" type="submit">Register</button>
        </form>
        <a class="link" href="/ethiotrip1/ethiotrip/public/login">Already have an account? Login</a>
    </div>
    <footer class="footer">
        <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
    </footer>
</body>
</html>
