<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserProgress;

/**
 * ExamController handles requests related to exams, exam sets, and questions.
 */
class ExamController extends Controller
{
    /**
     * Displays the main page for a specific exam.
     *
     * @param array $params The route parameters including 'exam'.
     * @return string The rendered view.
     */
    public function index(array $params): string
    {
        $examName = $params["exam"] ?? "Unknown Exam";

        // Retrieve user progress for the exam
        $userProgress = UserProgress::getProgressForExam(
            $this->getCurrentUserId(),
            $examName
        );

        return $this->render("exam/index", [
            "exam_name" => $examName,
            "breadcrumbs" => $this->getBreadcrumbs(),
            "user_progress" => $userProgress,
        ]);
    }

    /**
     * Displays the page for a specific exam set.
     *
     * @param array $params The route parameters including 'exam' and 'examset'.
     * @return string The rendered view.
     */
    public function examSet(array $params): string
    {
        $examName = $params["exam"] ?? "Unknown Exam";
        $examSetName = $params["examset"] ?? "Unknown Exam Set";

        // Retrieve user progress for the exam set
        $userProgress = UserProgress::getProgressForExamSet(
            $this->getCurrentUserId(),
            $examName,
            $examSetName
        );

        return $this->render("exam/examset", [
            "exam_name" => $examName,
            "examset_name" => $examSetName,
            "breadcrumbs" => $this->getBreadcrumbs(),
            "user_progress" => $userProgress,
        ]);
    }

    /**
     * Displays the page for a specific question within an exam set.
     *
     * @param array $params The route parameters including 'exam', 'examset', and 'question_number'.
     * @return string The rendered view.
     */
    public function question(array $params): string
    {
        $examName = $params["exam"] ?? "Unknown Exam";
        $examSetName = $params["examset"] ?? "Unknown Exam Set";
        $questionNumber = $params["question_number"] ?? "Unknown Question";

        // Retrieve user progress for the question
        $userProgress = UserProgress::getProgressForQuestion(
            $this->getCurrentUserId(),
            $examName,
            $examSetName,
            $questionNumber
        );

        return $this->render("exam/question", [
            "exam_name" => $examName,
            "examset_name" => $examSetName,
            "question_number" => $questionNumber,
            "breadcrumbs" => $this->getBreadcrumbs(),
            "user_progress" => $userProgress,
        ]);
    }

    /**
     * Resets user progress for a specific exam.
     *
     * @param array $params The route parameters including 'exam'.
     * @return string The response message.
     */
    public function resetExamProgress(array $params): string
    {
        $examId = $this->getExamIdByName($params["exam"] ?? "");

        if (!$examId) {
            return "Exam not found.";
        }

        $userProgress = new UserProgress();
        $userProgress->user_id = $this->getCurrentUserId();
        $success = $userProgress->resetProgressByExam($examId);

        return $success
            ? "Exam progress reset successfully."
            : "Failed to reset exam progress.";
    }

    /**
     * Resets user progress for a specific exam set.
     *
     * @param array $params The route parameters including 'exam' and 'examset'.
     * @return string The response message.
     */
    public function resetExamSetProgress(array $params): string
    {
        $examSetId = $this->getExamSetIdByName($params["examset"] ?? "");

        if (!$examSetId) {
            return "Exam set not found.";
        }

        $userProgress = new UserProgress();
        $userProgress->user_id = $this->getCurrentUserId();
        $success = $userProgress->resetProgressByExamSet($examSetId);

        return $success
            ? "Exam set progress reset successfully."
            : "Failed to reset exam set progress.";
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

    /**
     * Retrieves the exam ID based on the exam name.
     *
     * @param string $examName
     * @return int|null The exam ID or null if not found.
     */
    protected function getExamIdByName(string $examName): ?int
    {
        // Implement logic to retrieve exam ID from the database
        // Example:
        $exam = \App\Models\Exam::findByName($examName);
        return $exam->id ?? null;
    }

    /**
     * Retrieves the exam set ID based on the exam set name.
     *
     * @param string $examSetName
     * @return int|null The exam set ID or null if not found.
     */
    protected function getExamSetIdByName(string $examSetName): ?int
    {
        // Implement logic to retrieve exam set ID from the database
        // Example:
        $examSet = \App\Models\ExamSet::findByName($examSetName);
        return $examSet->id ?? null;
    }
}
