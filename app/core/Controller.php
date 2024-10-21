<?php

namespace App\Core;

use App\Models\UserProgress;

/**
 * Base Controller class providing common controller functionalities.
 */
class Controller
{
    /**
     * Retrieves the current user's ID.
     *
     * @return int The user ID.
     */
    protected function getCurrentUserId(): int
    {
        // Retrieve the user ID from the session, defaulting to 0 if not set.
        return $_SESSION["user_id"] ?? 0;
    }

    /**
     * Renders a view with the given parameters.
     *
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render(string $view, array $params = []): string
    {
        return Application::$app->router->renderView($view, $params);
    }

    /**
     * Retrieves breadcrumb data from the router.
     *
     * @return array
     */
    protected function getBreadcrumbs(): array
    {
        return Application::$app->router->getBreadcrumbs();
    }

    /**
     * Retrieves user progress data based on the provided parameters.
     *
     * @param string $examName The name of the exam.
     * @param string|null $examSetName The name of the exam set.
     * @param string|null $questionNumber The number of the question.
     * @return array The user progress data.
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
