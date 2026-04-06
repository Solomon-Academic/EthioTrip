<?php
require_once 'config/database.php';

echo "<h1>Testing Database Connection & Registration</h1>";

// Test connection
if ($conn) {
    echo "<p style='color:green'>✓ Database connected successfully</p>";
} else {
    echo "<p style='color:red'>✗ Database connection failed</p>";
    die();
}

// Test inserting a test user
$test_name = "Test User " . rand(100, 999);
$test_email = "test" . rand(100, 999) . "@example.com";
$test_phone = "0912345678";
$test_password = password_hash("password123", PASSWORD_DEFAULT);

$query = "INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'user')";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ssss", $test_name, $test_email, $test_password, $test_phone);

if (mysqli_stmt_execute($stmt)) {
    echo "<p style='color:green'>✓ Test user inserted successfully!</p>";
    echo "<p>Name: $test_name</p>";
    echo "<p>Email: $test_email</p>";
    echo "<p>Password: password123</p>";
} else {
    echo "<p style='color:red'>✗ Insert failed: " . mysqli_error($conn) . "</p>";
}

// Show all users in database
echo "<h2>Current Users in Database:</h2>";
$result = mysqli_query($conn, "SELECT id, name, email, phone, role, created_at FROM users");
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Created At</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found in database</p>";
}
?>