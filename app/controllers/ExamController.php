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
     * @param array $params Route parameters including 'exam'.
     * @return string Rendered view content.
     */
    public function index(array $params): string
    {
        $examName = $params["exam"] ?? "Unknown Exam";

        // Validate exam name
        if (
            !Validation::validatePattern('/^[a-zA-Z0-9\s_-]{3,50}$/', $examName)
        ) {
            return "Invalid exam name.";
        }

        // Fetch user progress for the specified exam
        $userProgress = $this->getUserProgress($examName);

        return $this->render("exam/index", [
            "exam_name" => $examName,
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

        // Validate exam set name
        if (
            !Validation::validatePattern(
                '/^[a-zA-Z0-9\s_-]{3,50}$/',
                $examSetName
            )
        ) {
            return "Invalid exam set name.";
        }

        // Fetch user progress for the specified exam set
        $userProgress = $this->getUserProgress($examName, $examSetName);

        return $this->render("exam/examset", [
            "exam_name" => $examName,
            "examset_name" => $examSetName,
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

        // Validate question number
        if (!Validation::validateInteger($questionNumber)) {
            return "Invalid question number.";
        }

        // Fetch user progress for the specified question
        $userProgress = $this->getUserProgress(
            $examName,
            $examSetName,
            $questionNumber
        );

        // Fetch explanations for the specific question
        $questionId = $this->getQuestionId($examSetName, $questionNumber);
        $explanations = Explanation::getExplanationsForQuestion($questionId);

        return $this->render("exam/question", [
            "exam_name" => $examName,
            "examset_name" => $examSetName,
            "question_number" => $questionNumber,
            "user_progress" => $userProgress,
            "explanations" => $explanations,
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
        $examName = $params["exam"] ?? "";

        // Validate exam name
        if (
            !Validation::validatePattern('/^[a-zA-Z0-9\s_-]{3,50}$/', $examName)
        ) {
            return "Invalid exam name.";
        }

        $examId = $this->getExamIdByName($examName);

        if (!$examId) {
            return "Exam not found.";
        }

        $userProgress = new UserProgress();
        $userProgress->user_id = $this->getCurrentUserId();
        $success = $userProgress->resetProgress($examId);

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
        $examSetName = $params["examset"] ?? "";

        // Validate exam set name
        if (
            !Validation::validatePattern(
                '/^[a-zA-Z0-9\s_-]{3,50}$/',
                $examSetName
            )
        ) {
            return "Invalid exam set name.";
        }

        $examSetId = $this->getExamSetIdByName($examSetName);

        if (!$examSetId) {
            return "Exam set not found.";
        }

        $userProgress = new UserProgress();
        $userProgress->user_id = $this->getCurrentUserId();
        $success = $userProgress->resetProgress(null, $examSetId);

        return $success
            ? "Exam set progress reset successfully."
            : "Failed to reset exam set progress.";
    }

    /**
     * Generates a new explanation for a specific question.
     *
     * @param array $params Route parameters including 'questionId'.
     * @return string JSON response indicating success or failure.
     */
    public function generateExplanation(array $params): string
    {
        $questionId = $params["questionId"] ?? null;

        if (!$questionId || !Validation::validateInteger($questionId)) {
            return json_encode([
                "success" => false,
                "message" => "Invalid question ID.",
            ]);
        }

        // Retrieve the model from environment variables
        $model = $_ENV["EXPLANATION_MODEL"] ?? "gpt-4o";

        // Placeholder for explanation generation logic
        // In a real scenario, an API call to the model would be made here

        // Create a new explanation entry with placeholder content
        $explanation = new Explanation();
        $explanation->question_id = (int) $questionId;
        $explanation->model = $model;
        $explanation->content = "This is a placeholder explanation generated by {$model}.";
        $explanation->prompt_tokens = 0; // Placeholder value
        $explanation->completion_tokens = 0; // Placeholder value

        if ($explanation->save()) {
            Logger::getInstance()->info(
                "New explanation generated for question ID {$questionId} using model {$model}."
            );
            return json_encode([
                "success" => true,
                "message" => "Explanation generated successfully.",
            ]);
        } else {
            Logger::getInstance()->error(
                "Failed to generate explanation for question ID {$questionId} using model {$model}."
            );
            return json_encode([
                "success" => false,
                "message" => "Failed to generate explanation.",
            ]);
        }
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

    /**
     * Retrieves the question ID based on exam set name and question number.
     *
     * @param string $examSetName Name of the exam set.
     * @param string $questionNumber Number of the question.
     * @return int|null Question ID or null if not found.
     */
    protected function getQuestionId(
        string $examSetName,
        string $questionNumber
    ): ?int {
        $examSet = \App\Models\ExamSet::findByName($examSetName);
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
}
