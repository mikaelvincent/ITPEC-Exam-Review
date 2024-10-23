<?php

namespace App\Core;

use App\Core\BreadcrumbGenerator;

/**
 * Base Controller class providing common functionalities for all controllers.
 */
class Controller
{
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
     * The session instance.
     *
     * @var Session
     */
    protected Session $session;

    /**
     * The router instance.
     *
     * @var Router
     */
    protected Router $router;

    /**
     * The breadcrumb generator instance.
     *
     * @var BreadcrumbGenerator
     */
    protected BreadcrumbGenerator $breadcrumbGenerator;

    /**
     * Controller constructor.
     *
     * Initializes the controller with core components.
     *
     * @param Request $request
     * @param Response $response
     * @param Session $session
     * @param Router $router
     * @param BreadcrumbGenerator $breadcrumbGenerator
     */
    public function __construct(
        Request $request,
        Response $response,
        Session $session,
        Router $router,
        BreadcrumbGenerator $breadcrumbGenerator
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->session = $session;
        $this->router = $router;
        $this->breadcrumbGenerator = $breadcrumbGenerator;
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
        $pathSegments = explode('/', trim($this->request->getUri(), '/'));
        $breadcrumbs = $this->breadcrumbGenerator->generate($pathSegments, $this->request->getBasePath());
        $params["breadcrumbs"] = $breadcrumbs;
        $params["basePath"] = $this->request->getBasePath();
        $params["request"] = $this->request;
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
        $breadcrumbs = $this->breadcrumbGenerator->generate(['error']);
        return $this->render("_error", [
            "message" => $message,
            "breadcrumbs" => $breadcrumbs,
        ]);
    }

    /**
     * Retrieves the current user's ID from the session.
     *
     * @return int User ID.
     */
    protected function getCurrentUserId(): int
    {
        return $this->session->get("user_id") ?? 0;
    }

    /**
     * Retrieves a model by its slug with validation.
     *
     * @param string $modelClass The model class name.
     * @param string $slug The slug to search for.
     * @return Model|null The model instance or null if not found.
     * @throws \Exception If the model class does not implement the required method.
     */
    protected function getModelBySlug(string $modelClass, string $slug): ?Model
    {
        if (!method_exists($modelClass, 'findByValidatedSlug')) {
            throw new \Exception("Method findByValidatedSlug not found in {$modelClass}");
        }
        return $modelClass::findByValidatedSlug($slug);
    }
}
