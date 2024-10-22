<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Exam;
use App\Models\UserProgress;

/**
 * HomeController manages requests related to the home page.
 */
class HomeController extends Controller
{
    /**
     * Renders the home page with dynamic user progress data.
     *
     * @return string Rendered view content.
     */
    public function index(): string
    {
        $userId = $this->getCurrentUserId();
        $exams = Exam::findAll();

        $examsData = [];
        foreach ($exams as $exam) {
            $examSets = $exam->getExamSets();
            if (empty($examSets)) {
                $examsData[] = [
                    "name" => $exam->name,
                    "slug" => $exam->slug,
                    "status" => "disabled",
                    "has_exam_sets" => false,
                ];
                continue;
            }

            $hasProgress = UserProgress::hasCompletedExam($userId, $exam->id);
            $isCompleted = $hasProgress;

            if ($isCompleted) {
                $status = "completed";
            } elseif ($hasProgress) {
                $status = "in_progress";
            } else {
                $status = "available";
            }

            $examsData[] = [
                "name" => $exam->name,
                "slug" => $exam->slug,
                "status" => $status,
                "has_exam_sets" => true,
            ];
        }

        return $this->render("home/index", [
            "exams_data" => $examsData,
        ]);
    }

    /**
     * Retrieves aggregated user progress data for display.
     *
     * @return array Aggregated user progress data.
     */
    protected function getUserProgressData(): array
    {
        $userId = $this->getCurrentUserId();
        return UserProgress::getUserProgressData($userId);
    }
}
