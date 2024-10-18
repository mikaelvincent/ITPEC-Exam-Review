<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;

/**
 * Answer model represents the `answer` table in the database.
 */
class Answer extends Model
{
    use Relationships;

    /**
     * The table associated with the Answer model.
     *
     * @var string
     */
    protected string $table = "answer";

    /**
     * Gets the question associated with the answer.
     *
     * @return Question|null The associated Question instance or null.
     */
    public function getQuestion(): ?Question
    {
        return $this->getRelatedModel(Question::class, "question_id");
    }
}