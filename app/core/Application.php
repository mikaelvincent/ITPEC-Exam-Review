<?php

namespace App\Core;

/**
 * Application class responsible for initializing and running the application.
 */
class Application
{
    /**
     * The current application instance.
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
     * An array of middleware to execute.
     *
     * @var array
     */
    public array $middleware = [];

    /**
     * Application constructor.
     *
     * Initializes the request, response, router, and middleware components.
     */
    public function __construct()
    {
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router();

        $this->registerRoutes();
    }

    /**
     * Registers application routes.
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        // Define default home route
        $this->router->get("/", "HomeController@index");

        // Define dynamic routes for exams, exam sets, and questions
        $this->router->get("/{exam}", "ExamController@index");
        $this->router->get("/{exam}/{examset}", "ExamController@examSet");
        $this->router->get(
            "/{exam}/{examset}/Q{question_number}",
            "ExamController@question"
        );

        // Route to reset exam progress
        $this->router->get("/{exam}/reset", "ExamController@resetExamProgress");

        // Route to reset exam set progress
        $this->router->get(
            "/{exam}/{examset}/reset",
            "ExamController@resetExamSetProgress"
        );
    }

    /**
     * Adds middleware to the application.
     *
     * @param callable $middleware The middleware to add.
     * @return void
     */
    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Runs the application by handling the incoming request and sending the response.
     * Executes registered middleware before resolving the route.
     *
     * @return void
     */
    public function run(): void
    {
        // Execute middleware
        foreach ($this->middleware as $middleware) {
            $middleware($this->request, $this->response);
        }

        $route = $this->router->resolve($this->request);
        echo $route;
    }

    /**
     * Retrieves a human-readable error title based on the status code.
     *
     * @param int $code
     * @return string
     */
    protected function getErrorTitle(int $code): string
    {
        $titles = [
            404 => "404 Not Found",
            500 => "500 Internal Server Error",
            401 => "401 Unauthorized",
        ];

        return $titles[$code] ?? "Error";
    }
}
