<?php
require_once 'database.php';

echo "<h1>Database Connection Test</h1>";

if ($conn) {
    echo "<p style='color:green'>✓ Database connected successfully!</p>";
    
    $result = mysqli_query($conn, "SHOW TABLES");
    echo "<h3><Tabl>es in database:</h3><ul>";
    while ($row = mysqli_fetch_array($result)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    $user_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
    $user_count = mysqli_fetch_assoc($user_result);
    echo "<p>Total users: " . $user_count['total'] . "</p>";
} else {
    echo "<p style='color:red'>✗ Database connection failed!</p>";
}
?>