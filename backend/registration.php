<?php
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name)) $errors['name'] = 'Full name is required';
    if (empty($email)) $errors['email'] = 'Email is required';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
    if (empty($phone)) $errors['phone'] = 'Phone number is required';
    if (empty($password)) $errors['password'] = 'Password is required';
    elseif (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters';
    if ($password !== $confirm_password) $errors['confirm_password'] = 'Passwords do not match';
    
    if (empty($errors)) {
        // Check if email exists
        $check = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors['email'] = 'Email already registered';
        }
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'user')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $phone);
        
        if (mysqli_stmt_execute($stmt)) {
            $user_id = mysqli_insert_id($conn);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'user';
            header('Location: dashboard.php');
            exit();
        } else {
            $errors['general'] = 'Registration failed: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .register-container { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); width: 100%; max-width: 450px; padding: 40px; }
        .logo { text-align: center; font-size: 28px; font-weight: 600; margin-bottom: 30px; text-decoration: none; display: block; color: #333; }
        .logo span { color: #d4af37; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #555; font-weight: 500; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        input:focus { outline: none; border-color: #d4af37; }
        .error { color: #e74c3c; font-size: 12px; margin-top: 5px; display: block; }
        .btn-register { width: 100%; padding: 12px; background: #d4af37; border: none; border-radius: 8px; color: white; font-weight: 600; font-size: 16px; cursor: pointer; }
        .btn-register:hover { background: #c09c2c; }
        .login-link { text-align: center; margin-top: 20px; color: #666; }
        .login-link a { color: #d4af37; text-decoration: none; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; background: #fee; color: #e74c3c; border: 1px solid #fcc; }
        .input-icon { position: relative; }
        .input-icon i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; }
        .input-icon input { padding-left: 35px; }
    </style>
</head>
<body>
    <div class="register-container">
        <a href="../frontend/home.html" class="logo">Ethio<span>Trip</span></a>
        
        <?php if (isset($errors['general'])): ?>
            <div class="alert"><?php echo $errors['general']; ?></div>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validateRegistrationForm()">
            <div class="form-group">
                <label>Full Name *</label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                </div>
                <span id="name-error" class="error"><?php echo $errors['name'] ?? ''; ?></span>
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                <span id="email-error" class="error"><?php echo $errors['email'] ?? ''; ?></span>
            </div>

            <div class="form-group">
                <label>Phone Number *</label>
                <div class="input-icon">
                    <i class="fas fa-phone"></i>
                    <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                </div>
                <span id="phone-error" class="error"><?php echo $errors['phone'] ?? ''; ?></span>
            </div>

            <div class="form-group">
                <label>Password * (min. 6 characters)</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" required>
                </div>
                <span id="password-error" class="error"><?php echo $errors['password'] ?? ''; ?></span>
            </div>

            <div class="form-group">
                <label>Confirm Password *</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <span id="confirm_password-error" class="error"><?php echo $errors['confirm_password'] ?? ''; ?></span>
            </div>

            <button type="submit" class="btn-register">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>

    <script src="../frontend/js/validation.js"></script>
</body>
</html>