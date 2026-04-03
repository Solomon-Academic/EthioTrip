<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EthioTrip - <?php echo $page_title ?? 'Home'; ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* Basic navigation styling (add this if style.css doesn't exist yet) */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 8%;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .logo {
            font-size: 1.6rem;
            font-weight: 600;
            text-decoration: none;
            color: #2d3436;
        }
        .logo span {
            color: #d4af37;
        }
        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: #2d3436;
            font-weight: 500;
        }
        .nav-links a:hover {
            color: #d4af37;
        }
        .welcome-text {
            color: #d4af37;
            font-weight: 600;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="logo">Ethio<span>Trip</span></a>
        <ul class="nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="bookings.php">My Bookings</a></li>
            <li><a href="../../frontend/packages.html">Packages</a></li>
            <li><a href="../../frontend/Destination.html">Destinations</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="logout.php">Logout</a></li>
                <li><span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span></li>
            <?php else: ?>
                <li><a href="login.php">Sign In</a></li>
                <li><a href="registration.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <main class="container">
        <?php displayFlashMessage(); ?>