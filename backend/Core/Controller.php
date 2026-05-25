<?php
namespace Backend\Core;

class Controller {
    protected $config = [];
    protected $basePath = '';

    public function __construct() {
        $this->config = require __DIR__ . '/../Config/config.php';
        $this->basePath = rtrim($this->config['base_path'] ?? '', '/');
    }

    protected function render($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "View not found: " . $view;
        }
    }
    
    protected function redirect($url) {
        if ($this->basePath === '') {
            $this->config = require __DIR__ . '/../Config/config.php';
            $this->basePath = rtrim($this->config['base_path'] ?? '', '/');
        }
        if (isset($url[0]) && $url[0] === '/' && ($this->basePath === '' || stripos($url, $this->basePath) !== 0)) {
            $url = $this->basePath . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    protected function csrfField() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(Session::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    protected function requireValidCsrf() {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            $this->json(['success' => false, 'message' => 'Invalid request token.'], 419);
        }
    }
    
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function requireLogin() {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }
    }
    
    protected function requireAdmin() {
        $this->requireLogin();
        if (!Session::isAdmin()) {
            $this->redirect('/dashboard');
        }
    }
}
?>
