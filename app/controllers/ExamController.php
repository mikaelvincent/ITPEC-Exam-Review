<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserProgress;
use App\Core\Validation;
use App\Models\Explanation;
use App\Core\Logger;

/**
 * Handles operations related to exams, exam sets, questions, and explanations.
 */
class ExamController extends Controller
{
    /**
     * Displays the main page for a specific exam.
     *
     * @param array $params Route parameters including 'slug'.
     * @return string Rendered view content.
     */
    public function index(array $params): string
    {
        $examSlug = $params["slug"] ?? "unknown-exam";

        // Validate exam slug
        if (
            !Validation::validatePattern('/^[a-zA-Z0-9\-]{3,255}$/', $examSlug)
        ) {
            return "Invalid exam slug.";
        }

        // Fetch user progress for the specified exam
        $userProgress = $this->getUserProgressBySlug($examSlug);

        return $this->render("exam/index", [
            "exam_slug" => $examSlug,
            "user_progress" => $userProgress,
        ]);
    }

    /**
     * Displays the page for a specific exam set.
     *
     * @param array $params Route parameters including 'slug' and 'examset_slug'.
     * @return string Rendered view content.
     */
    public function examSet(array $params): string
    {
        $examSlug = $params["slug"] ?? "unknown-exam";
        $examSetSlug = $params["examset_slug"] ?? "unknown-exam-set";

        // Validate exam set slug
        if (
            !Validation::validatePattern(
                '/^[a-zA-Z0-9\-]{3,255}$/',
                $examSetSlug
            )
        ) {
            return "Invalid exam set slug.";
        }

        // Fetch user progress for the specified exam set
        $userProgress = $this->getUserProgressBySlug($examSlug, $examSetSlug);

        return $this->render("exam/examset", [
            "exam_slug" => $examSlug,
            "examset_slug" => $examSetSlug,
            "user_progress" => $userProgress,
        ]);
    }

    /**
     * Displays the page for a specific question within an exam set.
     *
     * @param array $params Route parameters including 'slug', 'examset_slug', and 'question_number'.
     * @return string Rendered view content.
     */
    public function question(array $params): string
    {
        $examSlug = $params["slug"] ?? "unknown-exam";
        $examSetSlug = $params["examset_slug"] ?? "unknown-exam-set";
        $questionNumber = $params["question_number"] ?? "unknown-question";

        // Validate question number
        if (!Validation::validateInteger($questionNumber)) {
            return "Invalid question number.";
        }

        // Fetch user progress for the specified question
        $userProgress = $this->getUserProgressBySlug(
            $examSlug,
            $examSetSlug,
            $questionNumber
        );

        // Fetch explanations for the specific question
        $questionId = $this->getQuestionIdBySlug($examSetSlug, $questionNumber);
        $explanations = Explanation::getExplanationsForQuestion($questionId);

        return $this->render("exam/question", [
            "exam_slug" => $examSlug,
            "examset_slug" => $examSetSlug,
            "question_number" => $questionNumber,
            "user_progress" => $userProgress,
            "explanations" => $explanations,
        ]);
    }

    // ... [Other methods remain unchanged, updated to use slugs accordingly]

    /**
     * Retrieves the exam ID based on the exam slug.
     *
     * @param string $examSlug Slug of the exam.
     * @return int|null Exam ID or null if not found.
     */
    protected function getExamIdBySlug(string $examSlug): ?int
    {
        $exam = \App\Models\Exam::findBySlug($examSlug);
        return $exam->id ?? null;
    }

    /**
     * Retrieves the exam set ID based on the exam set slug.
     *
     * @param string $examSetSlug Slug of the exam set.
     * @return int|null Exam set ID or null if not found.
     */
    protected function getExamSetIdBySlug(string $examSetSlug): ?int
    {
        $examSet = \App\Models\ExamSet::findBySlug($examSetSlug);
        return $examSet->id ?? null;
    }

    /**
     * Retrieves the question ID based on exam set slug and question number.
     *
     * @param string $examSetSlug Slug of the exam set.
     * @param string $questionNumber Number of the question.
     * @return int|null Question ID or null if not found.
     */
    protected function getQuestionIdBySlug(
        string $examSetSlug,
        string $questionNumber
    ): ?int {
        $examSet = \App\Models\ExamSet::findBySlug($examSetSlug);
        if (!$examSet) {
            return null;
        }

        $question = \App\Models\Question::findAllBy(
            "exam_set_id",
            $examSet->id
        );
        foreach ($question as $q) {
            if ($q->question_number == (int) $questionNumber) {
                return $q->id;
            }
        }

        return null;
    }

    /**
     * Fetches user progress based on exam and optional exam set slugs.
     *
     * @param string $examSlug Slug of the exam.
     * @param string|null $examSetSlug Slug of the exam set.
     * @param string|null $questionNumber Number of the question.
     * @return array User progress information.
     */
    protected function getUserProgressBySlug(
        string $examSlug,
        ?string $examSetSlug = null,
        ?string $questionNumber = null
    ): array {
        $userId = $this->getCurrentUserId();

        if ($questionNumber !== null && $examSetSlug !== null) {
            return UserProgress::getProgressForQuestion(
                $userId,
                $examSlug,
                $examSetSlug,
                $questionNumber
            );
        }

        if ($examSetSlug !== null) {
            return UserProgress::getProgressForExamSetBySlug(
                $userId,
                $examSlug,
                $examSetSlug
            );
        }

        return UserProgress::getProgressForExamBySlug($userId, $examSlug);
    }
}
