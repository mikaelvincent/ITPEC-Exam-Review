<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;
use App\Core\Database;
use App\Core\Logger;

/**
 * Tracks user progress for specific exams and exam sets.
 */
class UserProgress extends Model
{
    use Relationships;

    /**
     * Associated database table name.
     *
     * @var string
     */
    protected string $table = "userprogress";

    /**
     * Resets progress based on provided criteria.
     *
     * Removes all active progress records related to the specified exam or exam set.
     *
     * @param int|null $examId ID of the exam.
     * @param int|null $examSetId ID of the exam set.
     * @return bool True if records were deleted, false otherwise.
     */
    public function resetProgress(
        ?int $examId = null,
        ?int $examSetId = null
    ): bool {
        $db = Database::getInstance();

        if ($examId !== null) {
            $sql = "DELETE up FROM {$this->table} up
                    JOIN answer a ON up.selected_answer_id = a.id
                    JOIN question q ON a.question_id = q.id
                    WHERE up.user_id = :user_id AND q.exam_set_id IN (
                        SELECT id FROM examset WHERE exam_id = :exam_id
                    )";
            return $db->execute($sql, [
                "user_id" => $this->user_id,
                "exam_id" => $examId,
            ]) > 0;
        }

        if ($examSetId !== null) {
            $sql = "DELETE FROM {$this->table}
                    WHERE user_id = :user_id AND selected_answer_id IN (
                        SELECT a.id FROM answer a
                        JOIN question q ON a.question_id = q.id
                        WHERE q.exam_set_id = :exam_set_id
                    )";
            return $db->execute($sql, [
                "user_id" => $this->user_id,
                "exam_set_id" => $examSetId,
            ]) > 0;
        }

        return false;
    }

    /**
     * Retrieves progress data for a specific exam.
     *
     * Aggregates the number of completed items for the given exam.
     *
     * @param int $userId ID of the user.
     * @param string $examName Name of the exam.
     * @return array Progress data.
     */
    public static function getProgressForExam(
        int $userId,
        string $examName
    ): array {
        $db = Database::getInstance();
        $instance = new static();
        $sql = "SELECT e.id AS exam_id, COUNT(up.id) AS completed
                FROM {$instance->table} up
                JOIN answer a ON up.selected_answer_id = a.id
                JOIN question q ON a.question_id = q.id
                JOIN examset es ON q.exam_set_id = es.id
                JOIN exam e ON es.exam_id = e.id
                WHERE up.user_id = :user_id
                  AND e.name = :exam_name
                  AND up.is_active = 1
                GROUP BY e.id";
        $results = $db->fetchAll($sql, [
            "user_id" => $userId,
            "exam_name" => $examName,
        ]);

        return $results;
    }

    /**
     * Retrieves progress data for a specific exam set.
     *
     * Aggregates the number of completed items for the given exam set.
     *
     * @param int $userId ID of the user.
     * @param string $examName Name of the exam.
     * @param string $examSetName Name of the exam set.
     * @return array Progress data.
     */
    public static function getProgressForExamSet(
        int $userId,
        string $examName,
        string $examSetName
    ): array {
        $db = Database::getInstance();
        $instance = new static();
        $sql = "SELECT es.id AS exam_set_id, COUNT(up.id) AS completed
                FROM {$instance->table} up
                JOIN answer a ON up.selected_answer_id = a.id
                JOIN question q ON a.question_id = q.id
                JOIN examset es ON q.exam_set_id = es.id
                JOIN exam e ON es.exam_id = e.id
                WHERE up.user_id = :user_id
                  AND e.name = :exam_name
                  AND es.name = :exam_set_name
                  AND up.is_active = 1
                GROUP BY es.id";
        $results = $db->fetchAll($sql, [
            "user_id" => $userId,
            "exam_name" => $examName,
            "exam_set_name" => $examSetName,
        ]);

        return $results;
    }

    /**
     * Retrieves progress data for a specific question.
     *
     * Provides detailed progress information for a particular question within an exam set.
     *
     * @param int $userId ID of the user.
     * @param string $examName Name of the exam.
     * @param string $examSetName Name of the exam set.
     * @param string $questionNumber Number of the question.
     * @return array Progress data.
     */
    public static function getProgressForQuestion(
        int $userId,
        string $examName,
        string $examSetName,
        string $questionNumber
    ): array {
        $db = Database::getInstance();
        $instance = new static();
        $sql = "SELECT up.*, q.question_number, a.content AS selected_answer
                FROM {$instance->table} up
                JOIN answer a ON up.selected_answer_id = a.id
                JOIN question q ON a.question_id = q.id
                JOIN examset es ON q.exam_set_id = es.id
                JOIN exam e ON es.exam_id = e.id
                WHERE up.user_id = :user_id 
                  AND e.name = :exam_name 
                  AND es.name = :exam_set_name 
                  AND q.question_number = :question_number
                  AND up.is_active = 1
                LIMIT 1";
        $result = $db->fetch($sql, [
            "user_id" => $userId,
            "exam_name" => $examName,
            "exam_set_name" => $examSetName,
            "question_number" => $questionNumber,
        ]);

        return $result ?? [];
    }

    /**
     * Retrieves aggregated user progress data for the dashboard.
     *
     * Compiles progress across all exams and exam sets for a user.
     *
     * @param int $userId ID of the user.
     * @return array Aggregated progress data.
     */
    public static function getUserProgressData(int $userId): array
    {
        $db = Database::getInstance();
        $instance = new static();
        $sql = "SELECT e.id AS exam_id, es.id AS exam_set_id, COUNT(up.id) AS completed
                FROM {$instance->table} up
                JOIN answer a ON up.selected_answer_id = a.id
                JOIN question q ON a.question_id = q.id
                JOIN examset es ON q.exam_set_id = es.id
                JOIN exam e ON es.exam_id = e.id
                WHERE up.user_id = :user_id
                  AND up.is_active = 1
                GROUP BY e.id, es.id";
        $results = $db->fetchAll($sql, [
            "user_id" => $userId,
        ]);

        return $results;
    }

    /**
     * Checks if the user has completed a specific exam.
     *
     * @param int $examId ID of the exam.
     * @return bool True if completed, false otherwise.
     */
    public function hasCompletedExam(int $examId): bool
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) AS count 
                FROM {$this->table} up
                JOIN answer a ON up.selected_answer_id = a.id
                JOIN question q ON a.question_id = q.id
                WHERE up.user_id = :user_id 
                  AND q.exam_set_id IN (
                      SELECT id FROM examset WHERE exam_id = :exam_id
                  )
                  AND up.is_active = 1";
        $result = $db->fetch($sql, [
            "user_id" => $this->user_id,
            "exam_id" => $examId,
        ]);

        return $result["count"] > 0;
    }

    /**
     * Checks if the user has completed a specific exam set.
     *
     * @param int $examSetId ID of the exam set.
     * @return bool True if completed, false otherwise.
     */
    public function hasCompletedExamSet(int $examSetId): bool
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) AS count 
                FROM {$this->table} up
                JOIN answer a ON up.selected_answer_id = a.id
                JOIN question q ON a.question_id = q.id
                WHERE up.user_id = :user_id 
                  AND q.exam_set_id = :exam_set_id
                  AND up.is_active = 1";
        $result = $db->fetch($sql, [
            "user_id" => $this->user_id,
            "exam_set_id" => $examSetId,
        ]);

        return $result["count"] > 0;
    }

    /**
     * Retrieves the user associated with this progress record.
     *
     * @return User|null Associated User instance or null if not found.
     */
    public function getUser(): ?User
    {
        return $this->getRelatedModel(User::class, "user_id");
    }

    /**
     * Retrieves the selected answer associated with this progress record.
     *
     * @return Answer|null Associated Answer instance or null if not found.
     */
    public function getSelectedAnswer(): ?Answer
    {
        return $this->getRelatedModel(Answer::class, "selected_answer_id");
    }
}
