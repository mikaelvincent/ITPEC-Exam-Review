<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;
use App\Core\Database;
use App\Core\Logger;

/**
 * UserProgress model represents the `userprogress` table in the database.
 * It tracks user progress for specific exams and exam sets.
 */
class UserProgress extends Model
{
    use Relationships;

    /**
     * The table associated with the UserProgress model.
     *
     * @var string
     */
    protected string $table = "userprogress";

    /**
     * Resets progress for a specific exam.
     *
     * @param int $examId The ID of the exam.
     * @return bool True on success, false otherwise.
     */
    public function resetProgressByExam(int $examId): bool
    {
        $db = Database::getInstance();
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

    /**
     * Resets progress for a specific exam set.
     *
     * @param int $examSetId The ID of the exam set.
     * @return bool True on success, false otherwise.
     */
    public function resetProgressByExamSet(int $examSetId): bool
    {
        $db = Database::getInstance();
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

    /**
     * Retrieves user progress data for a specific exam.
     *
     * @param int $userId The ID of the user.
     * @param string $examName The name of the exam.
     * @return array User progress data.
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
     * Retrieves user progress data for a specific exam set.
     *
     * @param int $userId The ID of the user.
     * @param string $examName The name of the exam.
     * @param string $examSetName The name of the exam set.
     * @return array User progress data.
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
     * Retrieves user progress data for a specific question.
     *
     * @param int $userId The ID of the user.
     * @param string $examName The name of the exam.
     * @param string $examSetName The name of the exam set.
     * @param string $questionNumber The number of the question.
     * @return array User progress data.
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
     * @param int $userId The ID of the user.
     * @return array Aggregated user progress data.
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
     * Determines if the user has completed a specific exam.
     *
     * @param int $examId The ID of the exam.
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
     * Determines if the user has completed a specific exam set.
     *
     * @param int $examSetId The ID of the exam set.
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
     * Gets the user associated with the progress record.
     *
     * @return User|null The associated User instance or null.
     */
    public function getUser(): ?User
    {
        return $this->getRelatedModel(User::class, "user_id");
    }

    /**
     * Gets the selected answer associated with the progress record.
     *
     * @return Answer|null The associated Answer instance or null.
     */
    public function getSelectedAnswer(): ?Answer
    {
        return $this->getRelatedModel(Answer::class, "selected_answer_id");
    }
}
