<?php
require_once 'config/database.php';

echo "<h1>Users Table Structure</h1>";

$result = $conn->query("DESCRIBE users");

if ($result) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>