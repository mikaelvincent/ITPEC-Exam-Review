<?php

namespace App\Core;

use App\Core\Request;
use App\Core\Response;

/**
 * Manages the execution of middleware in a pipeline.
 */
class MiddlewarePipeline
{
    /**
     * Array of middleware callables.
     *
     * @var array
     */
    protected array $middleware = [];

    /**
     * Adds a middleware to the pipeline.
     *
     * @param callable $middleware The middleware to add.
     * @return void
     */
    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Executes the middleware pipeline.
     *
     * @param Request $request The current request instance.
     * @param Response $response The current response instance.
     * @param callable $next The final action to execute after middleware.
     * @return mixed
     */
    public function handle(Request $request, Response $response, callable $next)
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            function ($next, $middleware) {
                return function ($request, $response) use ($next, $middleware) {
                    return $middleware($request, $response, $next);
                };
            },
            $next
        );

        return $pipeline($request, $response);
    }
}
