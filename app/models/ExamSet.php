<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;
use App\Core\Database;
use App\Core\Validation;
use App\Models\ExamSetRepository;

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
     * Finds an exam set by validated slug.
     *
     * @param string $slug The slug to validate and search for.
     * @return ExamSet|null The found exam set instance or null if not found.
     */
    public static function findByValidatedSlug(string $slug): ?ExamSet
    {
        if (!Validation::validateSlug($slug)) {
            return null;
        }

        $repository = new ExamSetRepository(Database::getInstance());
        return $repository->findBy('slug', $slug);
    }

    /**
     * Finds an exam set by its ID.
     *
     * @param int $id The ID of the exam set.
     * @return ExamSet|null The found exam set instance or null if not found.
     */
    public static function find(int $id): ?ExamSet
    {
        $repository = new ExamSetRepository(Database::getInstance());
        return $repository->find($id);
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

        $repository = new ExamSetRepository(Database::getInstance());
        return $repository->findAllBy($column, $value);
    }
}
