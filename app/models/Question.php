<?php

namespace App\Models;

use App\Core\Model;

/**
 * Question model represents the `question` table in the database.
 */
class Question extends Model
{
    /**
     * The table associated with the Question model.
     *
     * @var string
     */
    protected string $table = "question";

    /**
     * Gets the exam set associated with the question.
     *
     * @return ExamSet|null The associated ExamSet instance or null.
     */
    public function getExamSet(): ?ExamSet
    {
        return ExamSet::find($this->exam_set_id);
    }

    /**
     * Gets the answers associated with the question.
     *
     * @return array An array of Answer instances.
     */
    public function getAnswers(): array
    {
        return Answer::findAllByQuestionId($this->id);
    }
}
