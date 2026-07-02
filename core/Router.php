<?php

class Router {
    protected $routes = [];

    public function add($method, $path, $handler) {
        // Convert route like /api/orders/:id to a regular expression
        $route = preg_replace('/:([a-zA-Z0-9_]+)/', '(?P<$1>[^/]+)', $path);
        $route = '#^' . $route . '$#';
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => $route,
            'handler' => $handler
        ];
    }

    public function handle($requestUri, $requestMethod) {
        $parsedUrl = parse_url($requestUri);
        $path = $parsedUrl['path'] ?? '/';

        // Strip subfolder if running in a subdirectory (e.g. /kotapplication)
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if ($scriptDir !== '/' && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
        }

        // Strip index.php if accessed directly (e.g. /index.php/login)
        if (strpos($path, '/index.php') === 0) {
            $path = substr($path, 10);
        }

        // Standardize trailing slashes (except for '/')
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }
        if (empty($path)) {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($requestMethod) && preg_match($route['route'], $path, $matches)) {
                // Extract parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $this->execute($route['handler'], $params);
            }
        }

        // Check if API or view to return appropriate 404
        if (strpos($path, '/api/') === 0) {
            header("Content-Type: application/json");
            header("HTTP/1.0 404 Not Found");
            echo json_encode(['error' => 'Endpoint not found', 'path' => $path]);
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "<h1>404 Not Found</h1><p>The page you requested was not found.</p>";
        }
    }

    protected function execute($handler, $params) {
        if (is_callable($handler)) {
            return call_user_func_array($handler, [$params]);
        }

        list($controllerName, $action) = explode('@', $handler);
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            if (class_exists($controllerName)) {
                $controllerInstance = new $controllerName();
                if (method_exists($controllerInstance, $action)) {
                    return $controllerInstance->$action($params);
                }
            }
        }

        header("HTTP/1.0 500 Internal Server Error");
        echo "Controller Action '{$controllerName}@{$action}' not found.";
    }
}
