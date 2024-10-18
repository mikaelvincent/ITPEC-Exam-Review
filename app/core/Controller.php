<?php

namespace App\Core;

/**
 * Base Controller class providing common controller functionalities.
 */
class Controller
{
    /**
     * Renders a view with the given parameters.
     *
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render(string $view, array $params = []): string
    {
        return Application::$app->router->renderView($view, $params);
    }

    /**
     * Retrieves breadcrumb data from the router.
     *
     * @return array
     */
    protected function getBreadcrumbs(): array
    {
        return Application::$app->router->getBreadcrumbs();
    }
}
