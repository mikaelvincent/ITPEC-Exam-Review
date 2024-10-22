<?php

namespace App\Core;

/**
 * Route class encapsulates individual route details.
 */
class Route
{
    /**
     * HTTP method for the route.
     *
     * @var string
     */
    public string $method;

    /**
     * URI pattern for the route.
     *
     * @var string
     */
    public string $pattern;

    /**
     * Callback associated with the route.
     *
     * @var callable|string
     */
    public $callback;

    /**
     * Constructor for the Route class.
     *
     * @param string $method HTTP method.
     * @param string $pattern URI pattern.
     * @param callable|string $callback Callback function or controller action.
     */
    public function __construct(string $method, string $pattern, $callback)
    {
        $this->method = strtoupper($method);
        $this->pattern = "#^" . preg_replace(
            "/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/",
            '(?P<$1>[a-zA-Z0-9\-]+)',
            $pattern
        ) . '$#';
        $this->callback = $callback;
    }
}

/**
 * Router class handles routing of HTTP requests to appropriate controllers and actions.
 */
class Router
{
    /**
     * An array of defined routes.
     *
     * @var Route[]
     */
    protected array $routes = [];

    /**
     * The request instance.
     *
     * @var Request
     */
    protected Request $request;

    /**
     * The session instance.
     *
     * @var Session
     */
    protected Session $session;

    /**
     * The dependency injection container.
     *
     * @var Container
     */
    protected Container $container;

    /**
     * Router constructor.
     *
     * @param Request $request
     * @param Session $session
     * @param Container $container
     */
    public function __construct(Request $request, Session $session, Container $container)
    {
        $this->request = $request;
        $this->session = $session;
        $this->container = $container;
    }

    /**
     * Adds a new route.
     *
     * @param string $method HTTP method (GET, POST, etc.).
     * @param string $path URI path.
     * @param callable|string $callback Callback function or controller action.
     * @return void
     */
    public function addRoute(string $method, string $path, $callback): void
    {
        $this->routes[] = new Route($method, $path, $callback);
    }

    /**
     * Adds a new GET route.
     *
     * @param string $path
     * @param callable|string $callback
     * @return void
     */
    public function get(string $path, $callback): void
    {
        $this->addRoute('GET', $path, $callback);
    }

    /**
     * Resolves the current request to a route and executes the corresponding action.
     *
     * @param Request $request
     * @param Response $response
     * @return string
     * @throws \Exception
     */
    public function resolve(Request $request, Response $response): string
    {
        $method = $request->getMethod();
        $path = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route->method !== $method) {
                continue;
            }

            if (preg_match($route->pattern, $path, $matches)) {
                $params = array_filter(
                    $matches,
                    "is_string",
                    ARRAY_FILTER_USE_KEY
                );

                $callback = $route->callback;

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

                    // Use the container to instantiate the controller
                    $controller = $this->container->make($controllerName);

                    if (!method_exists($controller, $action)) {
                        throw new \Exception(
                            "Action $action not found in controller $controllerName",
                            500
                        );
                    }

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

        // Render the layout with the content embedded.
        ob_start();
        include $layoutPath;
        return ob_get_clean();
    }
}
