<?php

namespace App\Models;

use App\Core\Model;

/**
 * ExamSet model represents the `examset` table in the database.
 */
class ExamSet extends Model
{
    /**
     * The table associated with the ExamSet model.
     *
     * @var string
     */
    protected string $table = "examset";

    /**
     * Gets the exam associated with the exam set.
     *
     * @return Exam|null The associated Exam instance or null.
     */
    public function getExam(): ?Exam
    {
        return Exam::find($this->exam_id);
    }

    /**
     * Gets the questions associated with the exam set.
     *
     * @return array An array of Question instances.
     */
    public function getQuestions(): array
    {
        return Question::findAllByExamSetId($this->id);
    }
}
