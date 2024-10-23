<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;
use App\Core\Database;
use App\Core\Validation;
use App\Models\AnswerRepository;

/**
 * Question model represents the `question` table in the database.
 */
class Question extends Model
{
    use Relationships;

    /**
     * The table associated with the Question model.
     *
     * @var string
     */
    protected string $table = "question";

    /**
     * Validates the Question model's attributes.
     *
     * @return array Validation errors, empty if none.
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->exam_set_id) || !Validation::validateInteger($this->exam_set_id)) {
            $errors[] = "Invalid exam set ID.";
        }

        if (empty($this->question_number) || !Validation::validateInteger($this->question_number)) {
            $errors[] = "Invalid question number.";
        }

        return $errors;
    }

    /**
     * Gets the exam set associated with the question.
     *
     * @return ExamSet|null The associated ExamSet instance or null.
     */
    public function getExamSet(): ?ExamSet
    {
        return $this->getRelatedModel(ExamSet::class, "exam_set_id");
    }

    /**
     * Gets the answers associated with the question.
     *
     * @return array An array of Answer instances.
     */
    public function getAnswers(): array
    {
        $repository = new AnswerRepository(Database::getInstance());
        return $repository->findAllBy("question_id", $this->id);
    }

    /**
     * Finds all questions by a specific column and value.
     *
     * @param string $column Column name for filtering (e.g., 'exam_set_id').
     * @param mixed $value Value to match the column.
     * @return array An array of Question instances.
     */
    public static function findAllBy(string $column, $value): array
    {
        if (!in_array($column, ['exam_set_id', 'question_number'], true)) {
            throw new \InvalidArgumentException("Invalid column: $column");
        }

        $db = Database::getInstance();
        $sql = "SELECT * FROM question WHERE {$column} = :value";
        $rows = $db->fetchAll($sql, ['value' => $value]);

        $questions = [];
        foreach ($rows as $row) {
            $question = new self();
            $question->setAttributes($row);
            $questions[] = $question;
        }

        return $questions;
    }

    /**
     * Finds a question by validated exam set slug and question number.
     *
     * @param string $examSetSlug The slug of the exam set.
     * @param int $questionNumber The question number.
     * @return Question|null The found Question instance or null.
     */
    public static function findByValidatedExamSetSlugAndNumber(
        string $examSetSlug,
        int $questionNumber
    ): ?Question {
        if (
            !Validation::validateSlug($examSetSlug) ||
            !Validation::validateInteger($questionNumber)
        ) {
            return null;
        }
        $examSet = ExamSet::findByValidatedSlug($examSetSlug);
        if (!$examSet) {
            return null;
        }

        $db = Database::getInstance();
        $sql =
            "SELECT q.* FROM question q WHERE q.exam_set_id = :examSetId AND q.question_number = :questionNumber LIMIT 1";
        $data = $db->fetch($sql, [
            "examSetId" => $examSet->id,
            "questionNumber" => $questionNumber,
        ]);

        if ($data) {
            $question = new static();
            $question->attributes = $data;
            return $question;
        }

        return null;
    }
}
