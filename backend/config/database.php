<?php
// Set session cookie parameters to work across entire site
if (session_status() === PHP_SESSION_NONE) {
    // Make session work across all subdirectories
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',  // IMPORTANT: Makes session work everywhere
        'domain' => '',  // Current domain
        'secure' => false,  // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

$host = 'localhost';
$db   = 'ethiotrip_db';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>