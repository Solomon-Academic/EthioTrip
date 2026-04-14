<?php
session_start();
require_once 'config/database.php';

echo "<h1>Test Booking Display</h1>";

// Show all bookings
echo "<h2>All Bookings in Database:</h2>";
$result = mysqli_query($conn, "SELECT b.*, u.name as user_name FROM bookings b LEFT JOIN users u ON b.user_id = u.id ORDER BY b.id DESC");
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>User ID</th><th>User Name</th><th>Package</th><th>Amount</th><th>Status</th><th>Date</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['user_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['package_name']) . "</td>";
        echo "<td>$" . number_format($row['final_amount'], 2) . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>No bookings found in database!</p>";
}

// Show current logged-in user
echo "<h2>Current Session:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Show users
echo "<h2>Users in Database:</h2>";
$users = mysqli_query($conn, "SELECT id, name, email, trips_completed, total_spent FROM users");
if (mysqli_num_rows($users) > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Trips</th><th>Total Spent</th></tr>";
    while ($user = mysqli_fetch_assoc($users)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['trips_completed'] . "</td>";
        echo "<td>$" . number_format($user['total_spent'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>