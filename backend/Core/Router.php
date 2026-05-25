<?php
namespace Backend\Core;

class Router {
    private $routes = [];
    
    public function add($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function dispatch($method, $uri) {
        $uri = strtok($uri, '?');
        if ($uri === '') $uri = '/';
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                if (is_string($route['handler'])) {
                    $parts = explode('@', $route['handler']);
                    if (count($parts) === 2) {
                        $controllerName = 'Backend\\Controllers\\' . $parts[0];
                        $methodName = $parts[1];
                        
                        if (class_exists($controllerName)) {
                            $controller = new $controllerName();
                            if (method_exists($controller, $methodName)) {
                                call_user_func([$controller, $methodName]);
                                return;
                            }
                        }
                    }
                }
            }
        }
        
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        echo "<a href='/ethiotrip1/ethiotrip/public/'>Go Home</a>";
    }
}
?>