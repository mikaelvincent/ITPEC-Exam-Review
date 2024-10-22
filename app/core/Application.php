<?php

namespace App\Core;

use App\Core\Interfaces\LoggerInterface;

/**
 * Application class responsible for initializing and running the application.
 */
class Application
{
    /**
     * The singleton instance of the Application.
     *
     * @var Application
     */
    public static Application $app;

    /**
     * The current request instance.
     *
     * @var Request
     */
    public Request $request;

    /**
     * The current response instance.
     *
     * @var Response
     */
    public Response $response;

    /**
     * The router instance.
     *
     * @var Router
     */
    public Router $router;

    /**
     * The session instance.
     *
     * @var Session
     */
    public Session $session;

    /**
     * The middleware pipeline instance.
     *
     * @var MiddlewarePipeline
     */
    protected MiddlewarePipeline $middlewarePipeline;

    /**
     * Logger instance.
     *
     * @var LoggerInterface
     */
    public LoggerInterface $logger;

    /**
     * Application constructor.
     *
     * Initializes the request, response, router, session, and middleware components.
     */
    public function __construct()
    {
        self::$app = $this;
        $this->logger = Logger::getInstance();
        $this->session = new Session();
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->session);
        $this->middlewarePipeline = new MiddlewarePipeline();

        $this->registerRoutes();
    }

    /**
     * Registers application routes.
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        // Existing route registrations
    }

    /**
     * Adds middleware to the application.
     *
     * @param callable $middleware The middleware to add.
     * @return void
     */
    public function addMiddleware(callable $middleware): void
    {
        $this->middlewarePipeline->addMiddleware($middleware);
    }

    /**
     * Runs the application by handling the incoming request and sending the response.
     * Executes registered middleware before resolving the route.
     *
     * @return void
     */
    public function run(): void
    {
        $this->middlewarePipeline->handle(
            $this->request,
            $this->response,
            function ($request, $response) {
                $route = $this->router->resolve($request, $response);
                echo $route;
            }
        );
    }
}
