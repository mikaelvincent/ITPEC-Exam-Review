<?php

namespace App\Core;

/**
 * Base Controller class providing common controller functionalities.
 */
class Controller
{
    /**
     * Retrieves the current user's ID.
     *
     * @return int The user ID.
     */
    protected function getCurrentUserId(): int
    {
        // Retrieve the user ID from the session, defaulting to 0 if not set.
        return $_SESSION["user_id"] ?? 0;
    }

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
