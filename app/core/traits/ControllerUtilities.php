<?php

namespace App\Core\Traits;

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

    /**
     * Retrieves a model by its slug with validation.
     *
     * @param string $modelClass The model class name.
     * @param string $slug The slug to search for.
     * @return Model|null The model instance or null if not found.
     */
    protected function getModelBySlug(string $modelClass, string $slug): ?Model
    {
        if (!method_exists($modelClass, 'findByValidatedSlug')) {
            throw new \Exception("Method findByValidatedSlug not found in {$modelClass}");
        }
        return $modelClass::findByValidatedSlug($slug);
    }
}
