<?php

namespace App\Core;

use App\Core\Application;

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
     * Supports dynamic routes using placeholders in the format {parameter}.
     *
     * @param string $path
     * @param callable|string $callback
     * @return void
     */
    public function get(string $path, $callback): void
    {
        // Convert route path with placeholders to a regex pattern
        $routePattern = preg_replace(
            "/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/",
            '(?P<$1>[a-zA-Z0-9\-]+)',
            $path
        );
        $routePattern = "#^" . $routePattern . '$#';

        $this->routes["GET"][] = [
            "pattern" => $routePattern,
            "callback" => $callback,
        ];
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

        if (!isset($this->routes[$method])) {
            throw new \Exception("Page not found", 404);
        }

        foreach ($this->routes[$method] as $route) {
            if (preg_match($route["pattern"], $path, $matches)) {
                $params = array_filter(
                    $matches,
                    "is_string",
                    ARRAY_FILTER_USE_KEY
                );

                $callback = $route["callback"];

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

                    // Pass params to the controller action
                    return call_user_func_array(
                        [$controller, $action],
                        [$params]
                    );
                }

                if (is_callable($callback)) {
                    return call_user_func_array($callback, [$request, $params]);
                }

                throw new \Exception("Invalid route callback", 500);
            }
        }

        // No route matched
        throw new \Exception("Page not found", 404);
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
        $segments = explode("/", trim($path, "/"));
        $basePath = Application::$app->request->getBasePath();
        $currentPath = $basePath;
        $this->breadcrumbs = [
            [
                "title" => "Home",
                "path" => $basePath ?: "/",
            ],
        ];

        foreach ($segments as $segment) {
            if ($segment === "") {
                continue;
            }
            $currentPath .= "/" . $segment;
            // Replace hyphens with spaces and capitalize words for breadcrumb titles
            $title = ucwords(str_replace("-", " ", $segment));
            $this->breadcrumbs[] = [
                "title" => $title,
                "path" => $currentPath,
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
