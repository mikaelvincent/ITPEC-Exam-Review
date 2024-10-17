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
     * Application constructor.
     *
     * Initializes the request, response, and router components.
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
    }

    /**
     * Runs the application by handling the incoming request and sending the response.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $route = $this->router->resolve($this->request);
            echo $route;
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode() ?: 500);
            echo $this->router->renderView("_error", [
                "message" => $e->getMessage(),
                "breadcrumbs" => $this->router->getBreadcrumbs()
            ]);
        }
    }
}
