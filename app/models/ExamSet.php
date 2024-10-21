<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;

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
     * Finds an exam set by its slug.
     *
     * @param string $slug The slug of the exam set.
     * @return ExamSet|null The found ExamSet instance or null.
     */
    public static function findBySlug(string $slug): ?ExamSet
    {
        return self::findBy("slug", $slug);
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
        return $this->getRelatedModels(Question::class, "id");
    }
}
