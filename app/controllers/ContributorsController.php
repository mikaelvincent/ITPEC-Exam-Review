<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * ContributorsController manages requests related to the contributors page.
 */
class ContributorsController extends Controller
{
    /**
     * Renders the contributors page.
     *
     * @return string Rendered view content.
     */
    public function index(): string
    {
        return $this->render("contributors/index");
    }
}
