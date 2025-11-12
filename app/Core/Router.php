<?php

namespace app\Core;

class Router
{
    protected $routes = [
        'GET' => [],
        'POST' => []
    ];

    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }

    public function dispatch($uri, $method)
    {
        if (array_key_exists($uri, $this->routes[$method])) {
            return $this->callAction(
                ...explode('@', $this->routes[$method][$uri])
            );
        }

        http_response_code(404);
        require_once __DIR__ . '/../../views/errors/404.php';
    }

    protected function callAction($controller, $action)
    {
        $controller = "app\\Controllers\\{$controller}";
        $controllerInstance = new $controller;

        if (!method_exists($controllerInstance, $action)) {
            throw new \Exception("{$controller} does not respond to the {$action} action.");
        }

        return $controllerInstance->$action();
    }
}