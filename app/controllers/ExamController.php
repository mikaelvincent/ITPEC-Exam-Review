<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserProgress;
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

        // Fetch exam by validated slug
        $exam = Exam::findByValidatedSlug($examSlug);
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
     * @param array $params Route parameters including 'examset_slug'.
     * @return string Rendered view content.
     */
    public function examSet(array $params): string
    {
        $examSetSlug = $params["examset_slug"] ?? "unknown-exam-set";

        // Fetch exam set by validated slug
        $examSet = ExamSet::findByValidatedSlug($examSetSlug);
        if (!$examSet) {
            return "Exam set not found.";
        }

        // Fetch user progress for the specified exam set
        $userProgress = UserProgress::getProgressForExamSetBySlug(
            $this->getCurrentUserId(),
            $examSet->getExam()->slug,
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
     * @param array $params Route parameters including 'examset_slug' and 'question_number'.
     * @return string Rendered view content.
     */
    public function question(array $params): string
    {
        $examSetSlug = $params["examset_slug"] ?? "unknown-exam-set";
        $questionNumber = $params["question_number"] ?? "unknown-question";

        $question = Question::findByValidatedExamSetSlugAndNumber(
            $examSetSlug,
            (int) $questionNumber
        );
        if (!$question) {
            return "Question not found.";
        }

        // Fetch user progress for the specified question
        $userProgress = UserProgress::getProgressForQuestion(
            $this->getCurrentUserId(),
            $question->getExamSet()->getExam()->slug,
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
