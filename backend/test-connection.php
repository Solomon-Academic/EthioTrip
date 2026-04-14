<?php
require_once 'config/database.php';

echo "<h1>Database Connection Test</h1>";

// Test connection
if ($conn) {
    echo "<p style='color:green'>✓ Database connected successfully</p>";
} else {
    echo "<p style='color:red'>✗ Database connection failed</p>";
    die();
}

// Check tables
$tables = ['users', 'discount_tiers', 'packages', 'bookings'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color:green'>✓ Table '$table' exists</p>";
    } else {
        echo "<p style='color:red'>✗ Table '$table' is MISSING!</p>";
    }
}

// Show current bookings
echo "<h2>Current Bookings:</h2>";
$result = mysqli_query($conn, "SELECT * FROM bookings");
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Package</th><th>Amount</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['package_name'] . "</td>";
        echo "<td>$" . $row['final_amount'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No bookings found in database.</p>";
}

// Show users
echo "<h2>Users:</h2>";
$result = mysqli_query($conn, "SELECT id, name, email, trips_completed FROM users");
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Trips</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['trips_completed'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found.</p>";
}
?>