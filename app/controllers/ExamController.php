<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserProgress;
use App\Core\Validation;
use App\Models\Explanation;
use App\Models\Exam;
use App\Models\ExamSet;
use App\Models\Question;

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

        // Fetch exam by slug
        $exam = Exam::findBySlug($examSlug);
        if (!$exam) {
            return "Exam not found.";
        }

        // Fetch user progress for the specified exam
        $userProgress = UserProgress::getProgressForExamBySlug(
            $this->getCurrentUserId(),
            $examSlug
        );

        return $this->render("exam/index", [
            "exam" => $exam,
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
            !Validation::validatePattern('/^[a-zA-Z0-9\-]{3,255}$/', $examSetSlug)
        ) {
            return "Invalid exam set slug.";
        }

        // Fetch exam set by slug
        $examSet = ExamSet::findBySlug($examSetSlug);
        if (!$examSet) {
            return "Exam set not found.";
        }

        // Fetch user progress for the specified exam set
        $userProgress = UserProgress::getProgressForExamSetBySlug(
            $this->getCurrentUserId(),
            $examSlug,
            $examSetSlug
        );

        return $this->render("exam/examset", [
            "exam_set" => $examSet,
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

        // Fetch question by exam set slug and question number
        $question = Question::findByExamSetSlugAndNumber(
            $examSetSlug,
            (int) $questionNumber
        );
        if (!$question) {
            return "Question not found.";
        }

        // Fetch user progress for the specified question
        $userProgress = UserProgress::getProgressForQuestion(
            $this->getCurrentUserId(),
            $examSlug,
            $examSetSlug,
            $questionNumber
        );

        // Fetch explanations for the specific question
        $explanations = Explanation::getExplanationsForQuestion($question->id);

        return $this->render("exam/question", [
            "question" => $question,
            "user_progress" => $userProgress,
            "explanations" => $explanations,
        ]);
    }
}
