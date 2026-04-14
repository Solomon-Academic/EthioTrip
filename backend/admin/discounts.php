<?php
session_start();
require_once '../config/database.php';

// Simple admin check (you can expand this)
$is_admin = false;
if (isset($_SESSION['user_id'])) {
    $check = mysqli_query($conn, "SELECT role FROM users WHERE id = " . $_SESSION['user_id']);
    $user = mysqli_fetch_assoc($check);
    $is_admin = ($user && $user['role'] === 'admin');
}

if (!$is_admin) {
    header('Location: ../login.php');
    exit();
}

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_tier'])) {
        $id = $_POST['id'];
        $min_trips = $_POST['min_trips'];
        $max_trips = $_POST['max_trips'] ?: 'NULL';
        $discount_percent = $_POST['discount_percent'];
        $tier_name = $_POST['tier_name'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $query = "UPDATE discount_tiers SET min_trips = ?, max_trips = ?, discount_percent = ?, tier_name = ?, is_active = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iidsii", $min_trips, $max_trips, $discount_percent, $tier_name, $is_active, $id);
        mysqli_stmt_execute($stmt);
        $message = "Discount tier updated successfully!";
    }
    
    if (isset($_POST['add_tier'])) {
        $min_trips = $_POST['min_trips'];
        $max_trips = $_POST['max_trips'] ?: 'NULL';
        $discount_percent = $_POST['discount_percent'];
        $tier_name = $_POST['tier_name'];
        
        $query = "INSERT INTO discount_tiers (min_trips, max_trips, discount_percent, tier_name) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iids", $min_trips, $max_trips, $discount_percent, $tier_name);
        mysqli_stmt_execute($stmt);
        $message = "New tier added successfully!";
    }
}

// Get all tiers
$tiers = mysqli_query($conn, "SELECT * FROM discount_tiers ORDER BY min_trips ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Discount Management | EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f5f7fa; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .navbar { background: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .logo { font-size: 1.5rem; font-weight: 600; text-decoration: none; color: #2d3436; }
        .logo span { color: #d4af37; }
        h1 { margin-bottom: 2rem; color: #2d3436; }
        .card { background: white; border-radius: 15px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .card h2 { margin-bottom: 1rem; color: #2d3436; font-size: 1.3rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; }
        input, select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; width: 100%; }
        button { padding: 8px 16px; background: #d4af37; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        button:hover { background: #c09c2c; }
        .btn-add { background: #27ae60; }
        .btn-add:hover { background: #219a52; }
        .message { padding: 1rem; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 1rem; }
        .info-box { background: #e3f2fd; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #d4af37; }
        .status-active { color: #27ae60; font-weight: 600; }
        .status-inactive { color: #e74c3c; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
        .mt-2 { margin-top: 1rem; }
        .btn-warning { background: #e74c3c; }
        .btn-warning:hover { background: #c0392b; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #219a52; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="../dashboard.php" class="logo">Ethio<span>Trip</span> Admin</a>
        <div>
            <a href="../dashboard.php" style="margin-right: 1rem; color: #2d3436; text-decoration: none;">Dashboard</a>
            <a href="../logout.php" style="color: #e74c3c; text-decoration: none;">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <h1><i class="fas fa-tags"></i> Loyalty Discount Management</h1>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="info-box">
            <i class="fas fa-info-circle"></i> 
            <strong>How it works:</strong> Discounts are automatically calculated based on number of completed trips.
            Update the tiers below and all user discounts will be updated automatically.
            <br><br>
            <strong>💡 Tip for inflation/deflation:</strong> Simply update the discount percentages below - no code changes needed!
        </div>
        
        <div class="card">
            <h2><i class="fas fa-layer-group"></i> Current Discount Tiers</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tier Name</th>
                        <th>Min Trips</th>
                        <th>Max Trips</th>
                        <th>Discount %</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($tier = mysqli_fetch_assoc($tiers)): ?>
                    <form method="POST" style="display: contents;">
                        <tr>
                            <td><input type="text" name="tier_name" value="<?php echo htmlspecialchars($tier['tier_name']); ?>" required style="width: 100px;"></td>
                            <td><input type="number" name="min_trips" value="<?php echo $tier['min_trips']; ?>" required style="width: 80px;"></td>
                            <td><input type="number" name="max_trips" value="<?php echo $tier['max_trips']; ?>" placeholder="∞" style="width: 80px;"></td>
                            <td><input type="number" step="0.5" name="discount_percent" value="<?php echo $tier['discount_percent']; ?>" required style="width: 80px;"> %</td>
                            <td>
                                <select name="is_active" style="width: 100px;">
                                    <option value="1" <?php echo $tier['is_active'] ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo !$tier['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </td>
                            <td>
                                <input type="hidden" name="id" value="<?php echo $tier['id']; ?>">
                                <button type="submit" name="update_tier"><i class="fas fa-save"></i> Update</button>
                            </td>
                        </tr>
                    </form>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card">
            <h2><i class="fas fa-plus-circle"></i> Add New Tier</h2>
            <form method="POST">
                <div class="grid-4">
                    <input type="text" name="tier_name" placeholder="Tier Name (e.g., 'Legend')" required>
                    <input type="number" name="min_trips" placeholder="Min Trips" required>
                    <input type="number" name="max_trips" placeholder="Max Trips (empty = unlimited)">
                    <input type="number" step="0.5" name="discount_percent" placeholder="Discount %" required>
                </div>
                <button type="submit" name="add_tier" class="btn-add mt-2"><i class="fas fa-plus"></i> Add Tier</button>
            </form>
        </div>
        
        <div class="card">
            <h2><i class="fas fa-chart-line"></i> Quick Adjust for Inflation/Deflation</h2>
            <p>Adjust all active discounts by a percentage:</p>
            <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
                <form method="POST" action="adjust-discounts.php" style="display: inline;">
                    <input type="hidden" name="adjustment" value="5">
                    <button type="submit" class="btn-warning"><i class="fas fa-arrow-up"></i> +5% (Inflation)</button>
                </form>
                <form method="POST" action="adjust-discounts.php" style="display: inline;">
                    <input type="hidden" name="adjustment" value="-5">
                    <button type="submit" class="btn-success"><i class="fas fa-arrow-down"></i> -5% (Deflation)</button>
                </form>
                <form method="POST" action="adjust-discounts.php" style="display: inline;">
                    <input type="hidden" name="adjustment" value="10">
                    <button type="submit" class="btn-warning"><i class="fas fa-arrow-up"></i> +10% (High Inflation)</button>
                </form>
                <form method="POST" action="adjust-discounts.php" style="display: inline;">
                    <input type="hidden" name="adjustment" value="-10">
                    <button type="submit" class="btn-success"><i class="fas fa-arrow-down"></i> -10% (High Deflation)</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <h2><i class="fas fa-users"></i> User Statistics</h2>
            <?php
            $total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
            $avg_trips = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(trips_completed) as avg FROM users"))['avg'];
            $total_discount_given = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(loyalty_discount * total_spent) as total FROM users"))['total'];
            ?>
            <div class="grid-4" style="margin-top: 1rem;">
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3><?php echo round($avg_trips, 1); ?></h3>
                    <p>Avg Trips/User</p>
                </div>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3>$<?php echo number_format($total_discount_given ?? 0, 2); ?></h3>
                    <p>Total Discount Given</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>