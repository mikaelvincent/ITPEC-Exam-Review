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
     * @var Application|null
     */
    public static ?Application $app = null;

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
        // Static route for the contributors page
        $this->router->get("/contributors", "ContributorsController@index");

        // Define dynamic routes for exams, exam sets, and questions using slugs
        $this->router->get("/", "HomeController@index");
        $this->router->get("/{slug}", "ExamController@index");
        $this->router->get("/{slug}/{examset_slug}", "ExamController@examSet");
        $this->router->get(
            "/{slug}/{examset_slug}/Q{question_number}",
            "ExamController@question"
        );

        // Route to reset exam progress using slug
        $this->router->get("/{slug}/reset", "ExamController@resetExamProgress");

        // Route to reset exam set progress using slug
        $this->router->get(
            "/{slug}/{examset_slug}/reset",
            "ExamController@resetExamSetProgress"
        );

        // Route to generate explanations
        $this->router->get(
            "/generate-explanation/{questionId}",
            "ExamController@generateExplanation"
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
}
