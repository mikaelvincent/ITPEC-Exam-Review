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
        return $this->render("home/index");
    }
}
