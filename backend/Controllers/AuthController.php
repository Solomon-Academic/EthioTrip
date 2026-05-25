<?php

namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->userModel = new User();
    }

    public function loginAction(): void
    {
        $errors = [];
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireValidCsrf();
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($email === '') {
                $errors['email'] = 'Please enter your email address.';
            }
            if ($password === '') {
                $errors['password'] = 'Please enter your password.';
            }

            if (empty($errors)) {
                $user = $this->userModel->findByEmail($email);

                if ($user && password_verify($password, $user['password'])) {
                    Session::set('user_id', $user['id']);
                    Session::set('user_name', $user['name']);
                    Session::set('user_email', $user['email']);
                    Session::set('user_role', $user['role']);

                    $defaultUrl = ($user['role'] ?? '') === 'admin' ? '/admin/dashboard' : '/dashboard';
                    $redirectUrl = Session::get('return_url') ?: $defaultUrl;
                    Session::remove('return_url');
                    $this->redirect($redirectUrl);
                }

                $errors['general'] = 'Invalid email or password.';
            }
        }

        $this->render('auth.login', [
            'errors' => $errors,
            'email' => $email,
        ]);
    }

    public function registerAction(): void
    {
        $errors = [];
        $data = [
            'name' => '',
            'email' => '',
            'phone' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireValidCsrf();
            $data['name'] = trim($_POST['name'] ?? '');
            $data['email'] = trim($_POST['email'] ?? '');
            $data['phone'] = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($data['name'] === '') {
                $errors['name'] = 'Full name is required.';
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'A valid email is required.';
            }
            if ($data['phone'] === '') {
                $errors['phone'] = 'Phone number is required.';
            }
            if ($password === '') {
                $errors['password'] = 'Password is required.';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Password must be at least 6 characters.';
            }
            if ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match.';
            }

            if (empty($errors)) {
                if ($this->userModel->findByEmail($data['email'])) {
                    $errors['email'] = 'Email already exists.';
                }
            }

            if (empty($errors)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                $data['role'] = 'user';

                if ($this->userModel->create($data)) {
                    $user = $this->userModel->findByEmail($data['email']);
                    Session::set('user_id', $user['id']);
                    Session::set('user_name', $user['name']);
                    Session::set('user_email', $user['email']);
                    Session::set('user_role', $user['role']);
                    $this->redirect('/dashboard');
                }

                $errors['general'] = 'Registration failed. Please try again.';
            }
        }

        $this->render('auth.register', [
            'errors' => $errors,
            'form' => $data,
        ]);
    }

    public function logoutAction(): void
    {
        Session::destroy();
        $this->redirect('/');
    }

    public function checkLoginAction(): void
    {
        header('Content-Type: application/json');
        $isLoggedIn = (bool) Session::get('user_id');

        echo json_encode([
            'logged_in' => $isLoggedIn,
            'user_id' => Session::get('user_id'),
            'user_name' => Session::get('user_name'),
            'user_email' => Session::get('user_email'),
            'user_role' => Session::get('user_role') ?? 'user'
        ]);
        exit;
    }
}
