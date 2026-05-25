<?php
// backend/bootstrap.php

spl_autoload_register(function ($class) {
    $prefix = 'Backend\\';
    $base_dir = __DIR__ . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    } else {
        // Debug: Show what file was not found
        error_log("Class not found: " . $class . " - Looking for: " . $file);
    }
});

// Load Core files manually to ensure they exist
$coreFiles = [
    __DIR__ . '/Core/Session.php',
    __DIR__ . '/Core/Database.php',
    __DIR__ . '/Core/Model.php',
    __DIR__ . '/Core/Controller.php',
    __DIR__ . '/Core/Router.php',
];

foreach ($coreFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

use Backend\Services\Env;
use Backend\Core\Database;
use Backend\Core\SchemaMigrator;

Env::load(__DIR__ . '/../.env');

try {
    $db = Database::getInstance();
    SchemaMigrator::run($db);
} catch (\Throwable $e) {
    error_log('Schema migration skipped: ' . $e->getMessage());
}
?>