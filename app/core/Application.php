<?php

namespace App\Core;

/**
 * Application class responsible for initializing and running the application.
 */
class Application
{
    /**
     * The current request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * The current response instance.
     *
     * @var Response
     */
    protected $response;

    /**
     * The router instance.
     *
     * @var Router
     */
    protected $router;

    /**
     * Application constructor.
     *
     * Initializes the request, response, and router components.
     */
    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router();
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
            ]);
        }
    }
}
