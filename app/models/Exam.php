<?php

namespace App\Models;

use App\Core\Model;

/**
 * Exam model represents the `exam` table in the database.
 */
class Exam extends Model
{
    /**
     * The table associated with the Exam model.
     *
     * @var string
     */
    protected string $table = "exam";

    /**
     * Gets the exam sets associated with the exam.
     *
     * @return array An array of ExamSet instances.
     */
    public function getExamSets(): array
    {
        return ExamSet::findAllByExamId($this->id);
    }
}
