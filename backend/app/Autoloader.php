<?php
namespace App;

class Autoloader {
    public static function register() {
        spl_autoload_register([self::class, 'load']);
    }

    public static function load($class) {
        // Remove leading backslash
        $class = ltrim($class, '\\');

        // Build file path from namespace
        $path = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';

        if (file_exists($path)) {
            require $path;
            return true;
        }

        return false;
    }
}
?>
