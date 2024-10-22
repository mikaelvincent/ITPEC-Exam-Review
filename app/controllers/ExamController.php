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
     * Retrieves an exam by slug.
     *
     * @param string $examSlug
     * @return Exam|null
     */
    protected function getExamBySlug(string $examSlug): ?Exam
    {
        return $this->getModelBySlug(Exam::class, $examSlug);
    }

    /**
     * Retrieves an exam set by slug.
     *
     * @param string $examSetSlug
     * @return ExamSet|null
     */
    protected function getExamSetBySlug(string $examSetSlug): ?ExamSet
    {
        return $this->getModelBySlug(ExamSet::class, $examSetSlug);
    }

    /**
     * Displays the main page for a specific exam.
     *
     * @param array $params Route parameters including 'slug'.
     * @return string Rendered view content.
     */
    public function index(array $params): string
    {
        $examSlug = $params["slug"] ?? "unknown-exam";

        $exam = $this->getExamBySlug($examSlug);
        if (!$exam) {
            return $this->renderError("Exam not found.");
        }

        // Fetch all exam sets for the exam
        $examSets = $exam->getExamSets();
        if (empty($examSets)) {
            return $this->renderError("No exam sets available for this exam.");
        }

        return $this->render("exam/index", [
            "exam" => $exam,
            "exam_sets" => $examSets,
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

        $examSet = $this->getExamSetBySlug($examSetSlug);
        if (!$examSet) {
            return $this->renderError("Exam set not found.");
        }

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
            return $this->renderError("Question not found.");
        }

        $userProgress = UserProgress::getProgressForQuestion(
            $this->getCurrentUserId(),
            $question->getExamSet()->getExam()->slug,
            $examSetSlug,
            $questionNumber
        );

        $explanations = Explanation::getExplanationsForQuestion($question->id);

        return $this->render("exam/question", [
            "question" => $question,
            "user_progress" => $userProgress,
            "explanations" => $explanations,
        ]);
    }
}
