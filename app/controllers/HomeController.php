<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserProgress;

/**
 * Manages requests related to the home page.
 */
class HomeController extends Controller
{
    /**
     * Renders the home page with user progress data.
     *
     * @return string Rendered view content.
     */
    public function index(): string
    {
        // Retrieve user progress data for the dashboard
        $userProgressData = $this->getUserProgressData();

        return $this->render("home/index", [
            "user_progress_data" => $userProgressData,
        ]);
    }

    /**
     * Retrieves aggregated user progress data for display.
     *
     * @return array Aggregated user progress data.
     */
    protected function getUserProgressData(): array
    {
        $userId = $this->getCurrentUserId();
        return UserProgress::getUserProgressData($userId);
    }
}
