<?php
// registration 
require_once 'includes/auth.php';
requireGuest();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    
    // Validate name
    if (empty($name)) {
        $errors['name'] = 'Full name is required';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters';
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!validateEmail($email)) {
        $errors['email'] = 'Please enter a valid email address';
    } else {
        // Check if email already exists
        $check = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors['email'] = 'Email already registered';
        }
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (!validatePassword($password)) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    // Validate confirm password
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // Validate phone (optional)
    if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors['phone'] = 'Phone number must be 10 digits';
    }
    
    // If no errors, insert user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $phone);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            // Auto login after registration
            $user_id = mysqli_insert_id($conn);
            $_SESSION['user_id'] = $user_id;
            header('Location: dashboard.php');
            exit();
        } else {
            $errors['general'] = 'Registration failed. Please try again.';
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            padding: 40px;
        }
        
        .logo {
            text-align: center;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 30px;
            text-decoration: none;
            display: block;
            color: #333;
        }
        
        .logo span {
            color: #d4af37;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }
        
        .error {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .btn-register {
            width: 100%;
            padding: 12px;
            background: #d4af37;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            background: #c09c2c;
            transform: translateY(-2px);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: #d4af37;
            text-decoration: none;
            font-weight: 600;
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-error {
            background: #fee;
            color: #e74c3c;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #27ae60;
            border: 1px solid #cfc;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .input-icon input {
            padding-left: 35px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <a href="index.php" class="logo">Ethio<span>Trip</span></a>
        
        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error"><?php echo $errors['general']; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">Registration successful! Redirecting...</div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label>Full Name <span style="color: red;">*</span></label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                </div>
                <?php echo displayError($errors, 'name'); ?>
            </div>
            
            <div class="form-group">
                <label>Email Address <span style="color: red;">*</span></label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                <?php echo displayError($errors, 'email'); ?>
            </div>
            
            <div class="form-group">
                <label>Password <span style="color: red;">*</span></label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" required>
                </div>
                <?php echo displayError($errors, 'password'); ?>
            </div>
            
            <div class="form-group">
                <label>Confirm Password <span style="color: red;">*</span></label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" required>
                </div>
                <?php echo displayError($errors, 'confirm_password'); ?>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <div class="input-icon">
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="09XXXXXXXX">
                </div>
                <?php echo displayError($errors, 'phone'); ?>
            </div>
            
            <button type="submit" class="btn-register">Create Account</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>
    
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            let password = document.getElementById('password').value;
            if (password.length < 6) {
                alert('Password must be at least 6 characters');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>