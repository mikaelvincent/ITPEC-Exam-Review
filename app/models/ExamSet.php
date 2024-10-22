<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;
use App\Core\Database;
use App\Core\Validation;

/**
 * ExamSet model represents the `examset` table in the database.
 */
class ExamSet extends Model
{
    use Relationships;

    /**
     * The table associated with the ExamSet model.
     *
     * @var string
     */
    protected string $table = "examset";

    /**
     * Validates the ExamSet model's attributes.
     *
     * @return array Validation errors, empty if none.
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->exam_id) || !Validation::validateInteger($this->exam_id)) {
            $errors[] = "Invalid exam ID.";
        }

        if (empty($this->name)) {
            $errors[] = "Name is required.";
        }

        if (empty($this->slug) || !Validation::validateSlug($this->slug)) {
            $errors[] = "Invalid slug.";
        }

        return $errors;
    }

    /**
     * Gets the exam associated with the exam set.
     *
     * @return Exam|null The associated Exam instance or null.
     */
    public function getExam(): ?Exam
    {
        return $this->getRelatedModel(Exam::class, "exam_id");
    }

    /**
     * Gets the questions associated with the exam set.
     *
     * @return array An array of Question instances.
     */
    public function getQuestions(): array
    {
        return Question::findAllBy("exam_set_id", $this->id);
    }

    /**
     * Retrieves all exam sets associated with the given column and value.
     *
     * @param string $column Column name for filtering.
     * @param mixed $value Value to match the column.
     * @return array An array of ExamSet instances.
     */
    public static function findAllBy(string $column, $value): array
    {
        if (!in_array($column, ['exam_id', 'name', 'slug'], true)) {
            throw new \InvalidArgumentException("Invalid column: $column");
        }

        $db = Database::getInstance();
        $sql = "SELECT * FROM examset WHERE {$column} = :value";
        $rows = $db->fetchAll($sql, ['value' => $value]);

        $examSets = [];
        foreach ($rows as $row) {
            $examSet = new self();
            $examSet->setAttributes($row);
            $examSets[] = $examSet;
        }

        return $examSets;
    }
}
