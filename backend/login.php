<?php
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token first
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            // Check rate limiting
            $rateLimitCheck = checkLoginAttempts($email);
            if (!$rateLimitCheck['allowed']) {
                $error = $rateLimitCheck['message'];
            } else {
                $query = "SELECT id, name, email, password, role FROM users WHERE email = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);

                if ($user && password_verify($password, $user['password'])) {
                    // Clear login attempts on success
                    clearLoginAttempts($email);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];

                    // Check for return URL from frontend
                    if (isset($_SESSION['return_url'])) {
                        $return_url = $_SESSION['return_url'];
                        unset($_SESSION['return_url']);
                        header('Location: ' . $return_url);
                    } else {
                        header('Location: dashboard.php');
                    }
                    exit();
                } else {
                    // Record failed attempt
                    recordFailedLogin($email);
                    $error = 'Invalid email or password';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .login-container { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); width: 100%; max-width: 400px; padding: 40px; }
        .logo { text-align: center; font-size: 28px; font-weight: 600; margin-bottom: 30px; text-decoration: none; display: block; color: #333; }
        .logo span { color: #d4af37; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #555; font-weight: 500; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        input:focus { outline: none; border-color: #d4af37; }
        .btn-login { width: 100%; padding: 12px; background: #d4af37; border: none; border-radius: 8px; color: white; font-weight: 600; font-size: 16px; cursor: pointer; transition: 0.3s; }
        .btn-login:hover { background: #c09c2c; }
        .register-link { text-align: center; margin-top: 20px; color: #666; }
        .register-link a { color: #d4af37; text-decoration: none; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; background: #fee; color: #e74c3c; border: 1px solid #fcc; }
        .input-icon { position: relative; }
        .input-icon i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; }
        .input-icon input { padding-left: 35px; }
        .back-home { text-align: center; margin-top: 15px; }
        .back-home a { color: #999; text-decoration: none; font-size: 0.8rem; }
        .back-home a:hover { color: #d4af37; }
        .error { color: #e74c3c; font-size: 12px; margin-top: 5px; display: block; }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="../frontend/home.html" class="logo">Ethio<span>Trip</span></a>
        
        <?php if ($error): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" onsubmit="return validateLoginForm()">
            <?php echo csrfField(); ?>

            <div class="form-group">
                <label>Email Address</label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="email" required>
                </div>
                <span id="email-error" class="error"></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" required>
                </div>
                <span id="password-error" class="error"></span>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>
        <div class="register-link">
            Don't have an account? <a href="registration.php">Create Account</a>
        </div>
        <div class="back-home">
            <a href="../frontend/home.html"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>

    <script src="../frontend/js/validation.js"></script>
</body>
</html>