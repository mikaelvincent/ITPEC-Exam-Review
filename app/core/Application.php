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
     * The dependency injection container.
     *
     * @var Container
     */
    public Container $container;

    /**
     * Application constructor.
     *
     * Initializes the container and core components.
     */
    public function __construct()
    {
        self::$app = $this;

        // Initialize the container
        $this->container = new Container();

        // Bind core services
        $this->container->bind(LoggerInterface::class, Logger::class);
        $this->container->bind(Session::class);
        $this->container->bind(Request::class);
        $this->container->bind(Response::class);
        $this->container->bind(Router::class, function ($container) {
            return new Router(
                $container->make(Request::class),
                $container->make(Session::class),
                $container
            );
        });

        // Resolve core services
        $this->logger = $this->container->make(LoggerInterface::class);
        $this->session = $this->container->make(Session::class);
        $this->request = $this->container->make(Request::class);
        $this->response = $this->container->make(Response::class);
        $this->router = $this->container->make(Router::class);

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
