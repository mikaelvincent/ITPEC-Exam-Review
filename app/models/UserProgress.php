<?php

namespace App\Models;

use App\Core\Model;

/**
 * UserProgress model represents the `userprogress` table in the database.
 */
class UserProgress extends Model
{
    /**
     * The table associated with the UserProgress model.
     *
     * @var string
     */
    protected string $table = "userprogress";

    /**
     * Finds all UserProgress records by user ID.
     *
     * @param int $userId The user ID.
     * @return array An array of UserProgress instances.
     */
    public static function findAllByUserId(int $userId): array
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM userprogress WHERE user_id = :user_id";
        $rows = $db->fetchAll($sql, ["user_id" => $userId]);

        $models = [];
        foreach ($rows as $row) {
            $model = new self();
            $model->attributes = $row;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Gets the user associated with the progress record.
     *
     * @return User|null The associated User instance or null.
     */
    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }

    /**
     * Gets the question associated with the progress record.
     *
     * @return Question|null The associated Question instance or null.
     */
    public function getQuestion(): ?Question
    {
        return Question::find($this->question_id);
    }

    /**
     * Gets the selected answer associated with the progress record.
     *
     * @return Answer|null The associated Answer instance or null.
     */
    public function getSelectedAnswer(): ?Answer
    {
        return Answer::find($this->selected_answer_id);
    }
}
