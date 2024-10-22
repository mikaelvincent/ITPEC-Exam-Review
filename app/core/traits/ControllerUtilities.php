<?php

namespace App\Core\Traits;

use App\Core\Session;
use App\Core\Application;

/**
 * Provides utility methods for controllers to enhance reusability.
 */
trait ControllerUtilities
{
    /**
     * Retrieves the current user's ID from the session.
     *
     * @return int User ID.
     */
    protected function getCurrentUserId(): int
    {
        return Session::get("user_id") ?? 0;
    }

    /**
     * Retrieves breadcrumb navigation data.
     *
     * @return array Breadcrumbs for the current page.
     */
    protected function getBreadcrumbs(): array
    {
        return Application::$app->router->getBreadcrumbs();
    }
}
