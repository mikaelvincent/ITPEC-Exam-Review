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
     * Retrieves the question associated with the answer.
     *
     * @return Question|null The associated Question instance or null.
     */
    public function getQuestion(): ?Question
    {
        return $this->getRelatedModel(Question::class, "question_id");
    }

    /**
     * Gets the label of the answer.
     *
     * @return string|null The label of the answer or null if not set.
     */
    public function getLabel(): ?string
    {
        return $this->label ?? null;
    }

    /**
     * Sets the label of the answer.
     *
     * @param string $label The label to set.
     * @return void
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * Gets the content of the answer.
     *
     * @return string|null The content of the answer or null if not set.
     */
    public function getContent(): ?string
    {
        return $this->content ?? null;
    }

    /**
     * Sets the content of the answer.
     *
     * @param string $content The content to set.
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * Checks if the answer is correct.
     *
     * @return bool True if correct, false otherwise.
     */
    public function isCorrect(): bool
    {
        return (bool) ($this->is_correct ?? false);
    }

    /**
     * Sets the correctness of the answer.
     *
     * @param bool $isCorrect The correctness status to set.
     * @return void
     */
    public function setIsCorrect(bool $isCorrect): void
    {
        $this->is_correct = $isCorrect;
    }
}
