<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserProgress;

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
        // Retrieve user progress data for the dashboard
        $userProgressData = UserProgress::getUserProgressData(
            $this->getCurrentUserId()
        );

        return $this->render("home/index", [
            "breadcrumbs" => $this->getBreadcrumbs(),
            "user_progress_data" => $userProgressData,
        ]);
    }
}
