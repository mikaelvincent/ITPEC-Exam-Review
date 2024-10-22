<?php

namespace App\Core;

/**
 * Base Controller class providing common functionalities for all controllers.
 */
class Controller
{
    /**
     * The router instance.
     *
     * @var Router
     */
    protected Router $router;

    /**
     * The request instance.
     *
     * @var Request
     */
    protected Request $request;

    /**
     * The response instance.
     *
     * @var Response
     */
    protected Response $response;

    /**
     * Controller constructor.
     *
     * @param Router $router
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Router $router, Request $request, Response $response)
    {
        $this->router = $router;
        $this->request = $request;
        $this->response = $response;
    }

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
        return $this->router->renderView($view, $params);
    }

    /**
     * Renders an error view with the given message.
     *
     * @param string $message Error message to display.
     * @return string Rendered error view content.
     */
    protected function renderError(string $message): string
    {
        return $this->render("_error", [
            "message" => $message,
        ]);
    }

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
        return $this->router->getBreadcrumbs();
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
