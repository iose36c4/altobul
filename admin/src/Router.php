<?php

declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function get(string $path, array $action): void
    {
        $this->routes['GET'][$path] = $action;
    }

    public function post(string $path, array $action): void
    {
        $this->routes['POST'][$path] = $action;
    }

    public function put(string $path, array $action): void
    {
        $this->routes['PUT'][$path] = $action;
    }

    public function dispatch(string $uri, string $method): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        if (! file_exists(__DIR__ . '/../config.php') && $uri !== '/install') {
            header('Location: /install');
            exit;
        }

        if (file_exists(__DIR__ . '/../config.php') && $uri === '/install') {
            header('Location: /');
            exit;
        }

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $params = $this->matchRoute($uri, $method);

        if ($params === null) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            exit;
        }

        [$class, $actionMethod] = $params['action'];
        $controller = new $class();
        $controller->$actionMethod(...$params['params']);
    }

    private function matchRoute(string $uri, string $method): ?array
    {
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route => $action) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return ['action' => $action, 'params' => $params];
            }
        }

        return null;
    }
}
