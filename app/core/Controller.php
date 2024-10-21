<?php

namespace App\Core;

use App\Models\UserProgress;

/**
 * Base Controller class providing common functionalities for all controllers.
 */
class Controller
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
     * Renders a view with the provided parameters.
     *
     * Automatically includes breadcrumb navigation data.
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
     * Fetches user progress based on the provided parameters.
     *
     * @param string $examName Name of the exam.
     * @param string|null $examSetName Name of the exam set.
     * @param string|null $questionNumber Number of the question.
     * @return array User progress information.
     */
    protected function getUserProgress(
        string $examName,
        ?string $examSetName = null,
        ?string $questionNumber = null
    ): array {
        $userId = $this->getCurrentUserId();

        if ($questionNumber !== null && $examSetName !== null) {
            return UserProgress::getProgressForQuestion(
                $userId,
                $examName,
                $examSetName,
                $questionNumber
            );
        }

        if ($examSetName !== null) {
            return UserProgress::getProgressForExamSet(
                $userId,
                $examName,
                $examSetName
            );
        }

        return UserProgress::getProgressForExam($userId, $examName);
    }
}
