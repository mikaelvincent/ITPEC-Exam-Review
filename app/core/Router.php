<?php

namespace App\Core;

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
     * @var array An array to store breadcrumb data.
     */
    protected $breadcrumbs = [];

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

        // Initialize breadcrumbs with Home
        $this->breadcrumbs = [
            [
                'title' => 'Home',
                'path' => '/'
            ]
        ];

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

            // Generate breadcrumbs based on the path
            $this->generateBreadcrumbs($path);

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

        // Start output buffering for the main content.
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        // Determine if a layout is used and render it.
        $layoutPath = __DIR__ . "/../views/layout/main.php";

        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout not found", 500);
        }

        // Render the layout with the content embedded and breadcrumbs.
        ob_start();
        include $layoutPath;
        return ob_get_clean();
    }

    /**
     * Generates breadcrumb data based on the current path.
     *
     * @param string $path
     * @return void
     */
    protected function generateBreadcrumbs(string $path): void
    {
        $segments = explode('/', trim($path, '/'));
        $currentPath = '';
        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }
            $currentPath .= '/' . $segment;
            $this->breadcrumbs[] = [
                'title' => ucfirst($segment),
                'path' => $currentPath
            ];
        }
    }

    /**
     * Retrieves the breadcrumb data.
     *
     * @return array
     */
    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }
}
