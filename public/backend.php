<?php
// Compatibility shim for older links that used backend.php?page=...

$config = require __DIR__ . '/../backend/Config/config.php';
$basePath = rtrim($config['base_path'] ?? '', '/');
$page = $_GET['page'] ?? 'login';

$routes = [
    'login' => '/login',
    'register' => '/register',
    'logout' => '/logout',
    'dashboard' => '/dashboard',
    'bookings' => '/bookings',
    'create-booking' => '/bookings/create',
    'edit-booking' => '/bookings/edit',
    'delete-booking' => '/bookings/delete',
    'discounts' => '/admin/discounts',
];

$target = $routes[$page] ?? '/';
$query = $_GET;
unset($query['page']);

if (!empty($query)) {
    $target .= '?' . http_build_query($query);
}

header('Location: ' . $basePath . $target, true, 302);
exit;
?>
