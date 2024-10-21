<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserProgress;

/**
 * Handles operations related to exams, exam sets, and questions.
 */
class ExamController extends Controller
{
    /**
     * Displays the main page for a specific exam.
     *
     * @param array $params Route parameters including 'exam'.
     * @return string Rendered view content.
     */
    public function index(array $params): string
    {
        $examName = $params["exam"] ?? "Unknown Exam";

        // Fetch user progress for the specified exam
        $userProgress = $this->getUserProgress($examName);

        return $this->render("exam/index", [
            "exam_name" => $examName,
            "breadcrumbs" => $this->getBreadcrumbs(),
            "user_progress" => $userProgress,
        ]);
    }

    /**
     * Displays the page for a specific exam set.
     *
     * @param array $params Route parameters including 'exam' and 'examset'.
     * @return string Rendered view content.
     */
    public function examSet(array $params): string
    {
        $examName = $params["exam"] ?? "Unknown Exam";
        $examSetName = $params["examset"] ?? "Unknown Exam Set";

        // Fetch user progress for the specified exam set
        $userProgress = $this->getUserProgress($examName, $examSetName);

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
     * @param array $params Route parameters including 'exam', 'examset', and 'question_number'.
     * @return string Rendered view content.
     */
    public function question(array $params): string
    {
        $examName = $params["exam"] ?? "Unknown Exam";
        $examSetName = $params["examset"] ?? "Unknown Exam Set";
        $questionNumber = $params["question_number"] ?? "Unknown Question";

        // Fetch user progress for the specified question
        $userProgress = $this->getUserProgress(
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
     * @param array $params Route parameters including 'exam'.
     * @return string Response message indicating success or failure.
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
     * @param array $params Route parameters including 'exam' and 'examset'.
     * @return string Response message indicating success or failure.
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
     * Retrieves the exam ID based on the exam name.
     *
     * @param string $examName Name of the exam.
     * @return int|null Exam ID or null if not found.
     */
    protected function getExamIdByName(string $examName): ?int
    {
        $exam = \App\Models\Exam::findByName($examName);
        return $exam->id ?? null;
    }

    /**
     * Retrieves the exam set ID based on the exam set name.
     *
     * @param string $examSetName Name of the exam set.
     * @return int|null Exam set ID or null if not found.
     */
    protected function getExamSetIdByName(string $examSetName): ?int
    {
        $examSet = \App\Models\ExamSet::findByName($examSetName);
        return $examSet->id ?? null;
    }
}
