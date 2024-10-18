<?php

namespace App\Controllers;

use App\Core\Controller;

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

        return $this->render("exam/index", [
            "exam_name" => $examName,
            "breadcrumbs" => $this->getBreadcrumbs(),
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

        return $this->render("exam/examset", [
            "exam_name" => $examName,
            "examset_name" => $examSetName,
            "breadcrumbs" => $this->getBreadcrumbs(),
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

        return $this->render("exam/question", [
            "exam_name" => $examName,
            "examset_name" => $examSetName,
            "question_number" => $questionNumber,
            "breadcrumbs" => $this->getBreadcrumbs(),
        ]);
    }
}
