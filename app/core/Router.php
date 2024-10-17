<?php

namespace App\Core;

use App\Controllers\HomeController;

/**
 * Router class handles routing of HTTP requests to appropriate controllers and actions.
 */
class Router
{
    /**
     * @var array An array of defined routes.
     */
    protected $routes = [];

    /**
     * Adds a new GET route.
     *
     * @param string $path
     * @param callable|string $callback
     * @return void
     */
    public function get(string $path, $callback): void
    {
        $this->routes["GET"][$path] = $callback;
    }

    /**
     * Resolves the current request to a route and executes the corresponding action.
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function resolve(Request $request): string
    {
        $method = $request->getMethod();
        $path = $request->getUri();

        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            throw new \Exception("Route not found", 404);
        }

        if (is_string($callback)) {
            $parts = explode("@", $callback);
            $controllerName = "App\\Controllers\\" . $parts[0];
            $action = $parts[1] ?? "index";

            if (!class_exists($controllerName)) {
                throw new \Exception(
                    "Controller $controllerName not found",
                    500
                );
            }

            $controller = new $controllerName();

            if (!method_exists($controller, $action)) {
                throw new \Exception(
                    "Action $action not found in controller $controllerName",
                    500
                );
            }

            return $controller->$action();
        }

        if (is_callable($callback)) {
            return call_user_func($callback, $request);
        }

        throw new \Exception("Invalid route callback", 500);
    }

    /**
     * Renders a view.
     *
     * @param string $view
     * @param array $params
     * @return string
     */
    public function renderView(string $view, array $params = []): string
    {
        $viewPath = __DIR__ . "/../views/" . $view . ".php";

        if (!file_exists($viewPath)) {
            throw new \Exception("View $view not found", 500);
        }

        extract($params);

        ob_start();
        include $viewPath;
        return ob_get_clean();
    }
}
