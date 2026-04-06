<?php
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/auth.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EthioTrip - <?php echo $page_title ?? 'Home'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f7fa; min-height: 100vh; }
        .navbar { background: white; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 20px rgba(0,0,0,0.08); position: sticky; top: 0; z-index: 1000; flex-wrap: wrap; gap: 1rem; }
        .logo { font-size: 1.5rem; font-weight: 700; text-decoration: none; color: #2d3436; }
        .logo span { color: #d4af37; }
        .nav-links { display: flex; gap: 2rem; align-items: center; list-style: none; flex-wrap: wrap; }
        .nav-links a { text-decoration: none; color: #2d3436; font-weight: 500; transition: 0.3s; }
        .nav-links a:hover { color: #d4af37; }
        .welcome-text { background: linear-gradient(135deg, #d4af37, #f39c12); padding: 0.5rem 1rem; border-radius: 50px; color: white; font-weight: 600; font-size: 0.9rem; }
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem 5%; min-height: calc(100vh - 200px); }
        .form-container { background: white; border-radius: 20px; padding: 2rem; max-width: 600px; margin: 2rem auto; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .form-container h1 { margin-bottom: 1.5rem; color: #2d3436; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #555; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 1rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #d4af37; }
        .btn-primary { display: inline-block; padding: 0.75rem 1.5rem; background: #d4af37; color: white; text-decoration: none; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { background: #c09c2c; }
        .btn-secondary { display: inline-block; padding: 0.75rem 1.5rem; background: #6c757d; color: white; text-decoration: none; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-left: 0.5rem; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-small { display: inline-block; padding: 0.25rem 0.75rem; background: #d4af37; color: white; text-decoration: none; border-radius: 5px; font-size: 0.8rem; transition: 0.3s; margin: 0 0.25rem; }
        .btn-small:hover { background: #c09c2c; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .error { color: #e74c3c; font-size: 0.8rem; margin-top: 0.25rem; display: block; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .data-table { width: 100%; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .data-table th { background: #f8f9fa; padding: 1rem; text-align: left; font-weight: 600; color: #555; }
        .data-table td { padding: 1rem; border-bottom: 1px solid #eee; color: #666; }
        .status { padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #cce5ff; color: #004085; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .action-bar { margin-bottom: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap; }
        .no-data { text-align: center; padding: 3rem; background: white; border-radius: 20px; color: #999; }
        .discount-info { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: white; text-align: center; }
        .footer { background: #1a1a1a; color: #bbb; padding: 2rem 5%; text-align: center; margin-top: 3rem; }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; }
            .nav-links { justify-content: center; }
            .data-table { font-size: 0.8rem; overflow-x: auto; display: block; }
            .data-table th, .data-table td { padding: 0.5rem; }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <a href="dashboard.php" class="logo">Ethio<span>Trip</span></a>
    <ul class="nav-links">
        <li><a href="../frontend/home.html">Home</a></li>
        <li><a href="../frontend/Destination.html">Destinations</a></li>
        <li><a href="../frontend/packages.html">Packages</a></li>
        <?php if (isLoggedIn()): ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="bookings.php">My Bookings</a></li>
            <li><a href="logout.php">Logout</a></li>
            <li><span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></span></li>
        <?php else: ?>
            <li><a href="login.php">Sign In</a></li>
            <li><a href="registration.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>
<main class="container">