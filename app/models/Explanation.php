<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;

/**
 * Explanation model represents the `explanation` table in the database.
 */
class Explanation extends Model
{
    use Relationships;

    /**
     * The table associated with the Explanation model.
     *
     * @var string
     */
    protected string $table = "explanation";

    /**
     * Retrieves all explanations related to a specific question.
     *
     * @param int $questionId The ID of the question.
     * @return array An array of Explanation instances.
     */
    public static function getExplanationsForQuestion(int $questionId): array
    {
        return self::findAllBy("question_id", $questionId);
    }

    /**
     * Saves the current Explanation instance to the database.
     *
     * @return bool True on success, false otherwise.
     */
    public function save(): bool
    {
        // Additional validation can be added here if necessary
        return parent::save();
    }

    /**
     * Retrieves the question associated with the explanation.
     *
     * @return Question|null The associated Question instance or null.
     */
    public function getQuestion(): ?Question
    {
        return $this->getRelatedModel(Question::class, "question_id");
    }

    /**
     * Retrieves the user who requested the explanation.
     *
     * @return User|null The associated User instance or null.
     */
    public function getRequester(): ?User
    {
        return $this->getRelatedModel(User::class, "requester_user_id");
    }
}
