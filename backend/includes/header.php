<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EthioTrip - <?php echo $page_title ?? 'Home'; ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">Ethio<span>Trip</span></a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="destination/Destination.php">Destinations</a></li>
            <li><a href="packages/packages.php">Packages</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="bookings.php">My Bookings</a></li>
                <li><a href="logout.php">Logout</a></li>
                <li><span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span></li>
            <?php else: ?>
                <li><a href="login.php">Sign In</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <main class="container">