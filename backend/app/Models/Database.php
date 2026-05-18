<?php
namespace App\Models;

class Database {
    private static $conn = null;

    public static function connect() {
        if (self::$conn === null) {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $db = $_ENV['DB_NAME'] ?? 'ethiotrip_db';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';

            self::$conn = mysqli_connect($host, $user, $pass, $db);

            if (!self::$conn) {
                throw new \Exception("Database connection failed: " . mysqli_connect_error());
            }

            mysqli_set_charset(self::$conn, "utf8");
        }

        return self::$conn;
    }

    public static function query($sql, $types = '', $params = []) {
        $conn = self::connect();
        $stmt = mysqli_prepare($conn, $sql);

        if ($types && !empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    public static function execute($sql, $types = '', $params = []) {
        $conn = self::connect();
        $stmt = mysqli_prepare($conn, $sql);

        if ($types && !empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        return mysqli_stmt_execute($stmt);
    }

    public static function insert($sql, $types = '', $params = []) {
        self::execute($sql, $types, $params);
        return mysqli_insert_id(self::connect());
    }

    public static function lastInsertId() {
        return mysqli_insert_id(self::connect());
    }

    public static function getConnection() {
        return self::connect();
    }
}
?>
