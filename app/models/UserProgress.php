<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;

/**
 * UserProgress model represents the `userprogress` table in the database.
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
     * Gets the user associated with the progress record.
     *
     * @return User|null The associated User instance or null.
     */
    public function getUser(): ?User
    {
        return $this->getRelatedModel(User::class, "user_id");
    }

    /**
     * Gets the question associated with the progress record.
     *
     * @return Question|null The associated Question instance or null.
     */
    public function getQuestion(): ?Question
    {
        return $this->getRelatedModel(Question::class, "question_id");
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
