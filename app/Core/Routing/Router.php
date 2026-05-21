<?php

namespace Flex\Core\Routing;

use Flex\Core\Events\EventManager;

class Router
{
    protected array $routes = [];
    protected EventManager $events;

    public function __construct(EventManager $events)
    {
        $this->events = $events;
    }

    public function get(string $path, array $handler, array $middlewares = []): void
    {
        $this->routes['GET'][$path] = [
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function post(string $path, array $handler, array $middlewares = []): void
    {
        $this->routes['POST'][$path] = [
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function resolve(?string $path = null): void
    {
        $this->events->trigger('router.register', $this);

        if ($path === null) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            if ($scriptPath !== '/') {
                $path = str_replace($scriptPath, '', $path);
            }
        }

        $path = '/' . trim($path, '/');
        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routePath => $routeData) {
                $normRoutePath = '/' . trim($routePath, '/');

                $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $normRoutePath);
                $pattern = "#^" . $pattern . "$#";

                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches);

                    if (!empty($routeData['middlewares'])) {
                        foreach ($routeData['middlewares'] as $middlewareClass) {
                            if (class_exists($middlewareClass)) {
                                $middleware = new $middlewareClass();
                                if (method_exists($middleware, 'handle')) {
                                    $middleware->handle();
                                }
                            }
                        }
                    }

                    $handler = $routeData['handler'];
                    $controllerClass = $handler[0];
                    $methodName = $handler[1];

                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $methodName)) {
                            call_user_func_array([$controller, $methodName], $matches);
                            return;
                        }
                    }
                }
            }
        }

        http_response_code(404);
        echo "404 - Page Not Found (Path: " . htmlspecialchars($path) . ")";
    }
}