<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserProgress;
use App\Models\UserProgressRepository;
use App\Models\Exam;
use App\Models\ExamSet;
use App\Models\Question;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\BreadcrumbGenerator;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Application;

/**
 * Handles operations related to exams, exam sets, questions, and explanations.
 */
class ExamController extends Controller
{
    /**
     * @var UserProgressRepository
     */
    protected UserProgressRepository $userProgressRepository;

    /**
     * ExamController constructor.
     *
     * Initializes the UserProgressRepository.
     */
    public function __construct(
        Request $request,
        Response $response,
        Session $session,
        Router $router,
        BreadcrumbGenerator $breadcrumbGenerator
    ) {
        parent::__construct($request, $response, $session, $router, $breadcrumbGenerator);
        $this->userProgressRepository = new UserProgressRepository(Database::getInstance());
    }

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

        // Pass the exam name and slug to the view along with the exam sets
        return $this->render("exam/index", [
            "exam" => $exam,
            "exam_sets" => $examSets,
            "exam_name" => $exam->name,
            "exam_slug" => $exam->slug,
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

        // Fetch all questions for the exam set
        $questions = $examSet->getQuestions();

        // Get the exam slug
        $examSlug = $examSet->getExam()->slug;

        return $this->render("exam/examset", [
            "exam_set" => $examSet,
            "user_progress" => $userProgress,
            "questions" => $questions,
            "exam_slug" => $examSlug,
        ]);
    }

    /**
     * Displays or processes the page for a specific question within an exam set.
     *
     * @param array $params Route parameters including 'examset_slug' and 'question_number'.
     * @return string Rendered view content.
     */
    public function question(array $params): string
    {
        $examSetSlug = $params["examset_slug"] ?? "unknown-exam-set";
        $questionNumber = $params["question_number"] ?? "unknown-question";

        // Handle POST request for answer submission
        if ($this->request->getMethod() === 'POST') {
            return $this->handleAnswerSubmission($examSetSlug, $questionNumber);
        }

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

        // Determine the current question index and total questions for navigation
        $examSet = $question->getExamSet();
        $allQuestions = $examSet->getQuestions();
        $totalQuestions = count($allQuestions);
        $currentQuestionIndex = array_search($question, $allQuestions, true) + 1;
        $nextQuestion = $allQuestions[$currentQuestionIndex] ?? null;
        $nextQuestionUrl = $nextQuestion
            ? "/{$examSet->getExam()->slug}/{$examSet->slug}/Q{$nextQuestion->question_number}"
            : "/{$examSet->getExam()->slug}/congratulations";

        return $this->render("exam/question", [
            "question" => $question,
            "user_progress" => $userProgress,
            "currentQuestionIndex" => $currentQuestionIndex,
            "totalQuestions" => $totalQuestions,
            "nextQuestionUrl" => $nextQuestionUrl,
        ]);
    }

    /**
     * Handles the submission of an answer for a specific question.
     *
     * @param string $examSetSlug Slug of the exam set.
     * @param string $questionNumber Number of the question.
     * @return string Rendered view content after processing.
     */
    protected function handleAnswerSubmission(string $examSetSlug, string $questionNumber): string
    {
        // Log the initiation of answer submission handling
        Application::$app->logger->info("Handling answer submission.", [  // <-- Fixed reference
            'examSetSlug' => $examSetSlug,
            'questionNumber' => $questionNumber,
            'userId' => $this->getCurrentUserId(),
        ]);

        // Sanitize inputs
        $examSetSlug = htmlspecialchars($examSetSlug);
        $questionNumber = (int) $questionNumber;

        try {
            // Retrieve the question
            $question = Question::findByValidatedExamSetSlugAndNumber(
                $examSetSlug,
                $questionNumber
            );

            if (!$question) {
                Application::$app->logger->warning("Question not found during answer submission.", [
                    'examSetSlug' => $examSetSlug,
                    'questionNumber' => $questionNumber,
                ]);
                return $this->renderError("Question not found.");
            }

            // Retrieve the selected answer ID from POST data
            $selectedAnswerId = (int) ($this->request->getPost('selected_answer_id') ?? 0);
            Application::$app->logger->info("Selected answer ID retrieved.", [
                'selectedAnswerId' => $selectedAnswerId,
            ]);

            // Validate the selected answer
            $selectedAnswer = null;
            foreach ($question->getAnswers() as $answer) {
                if ($answer->id === $selectedAnswerId) {
                    $selectedAnswer = $answer;
                    break;
                }
            }

            if (!$selectedAnswer) {
                Application::$app->logger->warning("Invalid answer selection.", [
                    'selectedAnswerId' => $selectedAnswerId,
                    'questionId' => $question->id,
                ]);
                return $this->renderError("Invalid answer selection.", 400);
            }            

            // Check if the user has already answered
            $existingProgress = UserProgress::getProgressForQuestion(
                $this->getCurrentUserId(),
                $question->getExamSet()->getExam()->slug,
                $examSetSlug,
                $questionNumber
            );

            if (!empty($existingProgress)) {
                Application::$app->logger->info("User has already answered this question.", [
                    'userId' => $this->getCurrentUserId(),
                    'questionId' => $question->id,
                ]);
                return $this->renderError("You have already answered this question.");
            }

            // Create a new UserProgress record
            $userProgress = new UserProgress();
            $userProgress->user_id = $this->getCurrentUserId();
            $userProgress->selected_answer_id = $selectedAnswerId;
            $userProgress->is_active = true;

            // Validate the UserProgress model
            $validationErrors = $userProgress->validate();
            if (!empty($validationErrors)) {
                Application::$app->logger->warning("UserProgress validation failed.", [
                    'errors' => $validationErrors,
                    'userProgress' => $userProgress->getAttributes(),
                ]);
                return $this->renderError(implode(" ", $validationErrors));
            }

            // Save the UserProgress using the repository
            if ($this->userProgressRepository->insertUserProgress($userProgress)) {
                Application::$app->logger->info("UserProgress inserted successfully.", [
                    'userId' => $userProgress->user_id,
                    'selectedAnswerId' => $userProgress->selected_answer_id,
                ]);
                // Redirect to the same question page to reflect the submitted answer
                $this->response->redirect($this->request->getUri());
                return '';
            } else {
                Application::$app->logger->error("Failed to insert UserProgress.", [
                    'userId' => $userProgress->user_id,
                    'selectedAnswerId' => $userProgress->selected_answer_id,
                ]);
                return $this->renderError("Failed to submit your answer. Please try again.");
            }
        } catch (\Exception $e) {
            // Log the exception details
            Application::$app->logger->error("Exception occurred during answer submission.", [
                'message' => $e->getMessage(),
                'stackTrace' => $e->getTraceAsString(),
            ]);
            // Rethrow the exception to be handled by the global exception handler
            throw $e;
        }
    }
}
