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
        $userProgressData = $this->getUserProgressData();

        return $this->render("home/index", [
            "breadcrumbs" => $this->getBreadcrumbs(),
            "user_progress_data" => $userProgressData,
        ]);
    }

    /**
     * Retrieves user progress data for the home dashboard.
     *
     * @return array
     */
    protected function getUserProgressData(): array
    {
        $userId = $this->getCurrentUserId();
        $progress = UserProgress::findAllBy("user_id", $userId);

        $progressData = [];
        foreach ($progress as $p) {
            $progressData[] = [
                "exam_id" => $p->exam_id,
                "exam_set_id" => $p->exam_set_id,
                "is_completed" => $p->is_completed,
            ];
        }

        return $progressData;
    }

    /**
     * Retrieves the current user's ID.
     *
     * @return int The user ID.
     */
    protected function getCurrentUserId(): int
    {
        // Placeholder for actual user authentication logic
        return $_SESSION["user_id"] ?? 0;
    }
}
