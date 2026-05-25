<?php
// public/index.php - Front Controller

require_once __DIR__ . '/../backend/bootstrap.php';

use Backend\Core\Router;
use Backend\Core\Session;

Session::start();

$requestUri = $_SERVER['REQUEST_URI'];
$config = require __DIR__ . '/../backend/Config/config.php';
$basePath = rtrim($config['base_path'] ?? '', '/');
$route = preg_replace('#^' . preg_quote($basePath, '#') . '#i', '', $requestUri, 1);
$route = strtok($route, '?');
if ($route === '' || $route === '/') $route = '/';

$router = new Router();
$routes = require __DIR__ . '/../routes/web.php';

foreach ($routes as $method => $routeList) {
    foreach ($routeList as $path => $handler) {
        $router->add($method, $path, $handler);
    }
}

$router->dispatch($_SERVER['REQUEST_METHOD'], $route);
?>
