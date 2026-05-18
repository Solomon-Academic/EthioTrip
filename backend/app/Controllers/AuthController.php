<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Database;

class AuthController {

    public static function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['status' => 'idle'];
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [
                'status' => 'error',
                'message' => 'Security validation failed. Please try again.',
                'errors' => ['general' => 'Invalid CSRF token']
            ];
        }

        $errors = [];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Validate inputs
        if (empty($name)) {
            $errors['name'] = 'Full name is required';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }

        if (empty($phone) || !preg_match('/^09[0-9]{8}$/', $phone)) {
            $errors['phone'] = 'Valid Ethiopian phone number required';
        }

        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password must have 8+ characters, uppercase, and number';
        }

        if ($password !== $confirm) {
            $errors['confirm'] = 'Passwords do not match';
        }

        // Check if email exists
        if (empty($errors) && User::findByEmail($email)) {
            $errors['email'] = 'Email already registered';
        }

        if (!empty($errors)) {
            return [
                'status' => 'validation_error',
                'errors' => $errors
            ];
        }

        // Create user
        if (User::create($name, $email, $phone, $password)) {
            $user = User::findByEmail($email);

            // Set session
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_name'] = $user->getName();
            $_SESSION['user_role'] = $user->getRole();

            return [
                'status' => 'success',
                'message' => 'Registration successful!',
                'redirect' => 'dashboard.php'
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Registration failed. Please try again.',
            'errors' => ['general' => 'Database error']
        ];
    }

    public static function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['status' => 'idle'];
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [
                'status' => 'error',
                'message' => 'Security validation failed. Please try again.'
            ];
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            return [
                'status' => 'error',
                'message' => 'Please fill in all fields'
            ];
        }

        // Find user
        $user = User::findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            return [
                'status' => 'error',
                'message' => 'Invalid email or password'
            ];
        }

        // Set session
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['user_role'] = $user->getRole();

        return [
            'status' => 'success',
            'message' => 'Login successful!',
            'redirect' => $_SESSION['return_url'] ?? 'dashboard.php'
        ];
    }

    public static function logout() {
        session_destroy();
        return [
            'status' => 'success',
            'redirect' => '../frontend/home.html'
        ];
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            $_SESSION['return_url'] = $_SERVER['REQUEST_URI'];
            header('Location: login.php');
            exit();
        }
    }

    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        return User::findById($_SESSION['user_id']);
    }
}
?>
