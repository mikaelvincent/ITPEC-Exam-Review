<?php

namespace App\Core;

use App\Core\Traits\ControllerUtilities;

/**
 * Base Controller class providing common functionalities for all controllers.
 */
class Controller
{
    use ControllerUtilities;

    /**
     * Renders a view with the provided parameters.
     *
     * @param string $view Path to the view file.
     * @param array $params Parameters to pass to the view.
     * @return string Rendered view content.
     */
    public function render(string $view, array $params = []): string
    {
        $params["breadcrumbs"] = $this->getBreadcrumbs();
        return Application::$app->router->renderView($view, $params);
    }
}
