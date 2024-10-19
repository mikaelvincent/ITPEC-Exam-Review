<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;

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
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id AND exam_id = :exam_id";
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
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id AND exam_set_id = :exam_set_id";
        return $db->execute($sql, [
            "user_id" => $this->user_id,
            "exam_set_id" => $examSetId,
        ]) > 0;
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
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id AND exam_id = :exam_id AND is_completed = 1";
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
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id AND exam_set_id = :exam_set_id AND is_completed = 1";
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
