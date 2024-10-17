<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * HomeController handles requests to the home page.
 */
class HomeController extends Controller
{
    /**
     * Renders the home page.
     *
     * @return string
     */
    public function index(): string
    {
        $breadcrumbs = [
            [
                'title' => 'Home',
                'path' => '/'
            ]
        ];
        return $this->render("home/index", ['breadcrumbs' => $this->getBreadcrumbs()]);
    }

    /**
     * Retrieves breadcrumb data from the router.
     *
     * @return array
     */
    protected function getBreadcrumbs(): array
    {
        return \App\Core\Application::$app->router->getBreadcrumbs();
    }
}
