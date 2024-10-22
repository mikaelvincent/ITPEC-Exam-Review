<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;
use App\Core\Validation;
use App\Core\Database;
use App\Models\ExamRepository;

/**
 * Exam model represents the `exam` table in the database.
 */
class Exam extends Model
{
    use Relationships;

    /**
     * The table associated with the Exam model.
     *
     * @var string
     */
    protected string $table = "exam";

    /**
     * Validates the Exam model's attributes.
     *
     * @return array Validation errors, empty if none.
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = "Name is required.";
        }

        if (empty($this->slug) || !Validation::validateSlug($this->slug)) {
            $errors[] = "Invalid slug.";
        }

        return $errors;
    }

    /**
     * Gets the exam sets associated with the exam.
     *
     * @return array An array of ExamSet instances.
     */
    public function getExamSets(): array
    {
        return ExamSet::findAllBy("exam_id", $this->id);
    }

    /**
     * Checks if the user has progress in this exam.
     *
     * @param int $userId ID of the user.
     * @return array Contains 'hasProgress' and 'isCompleted' keys.
     */
    public function hasUserProgress(int $userId): array
    {
        $userProgress = new UserProgress();
        $userProgress->user_id = $userId;
        $hasProgress = $userProgress->hasCompletedExam($this->id);
        $isCompleted = $hasProgress;

        return [
            'hasProgress' => $hasProgress,
            'isCompleted' => $isCompleted,
        ];
    }

    /**
     * Retrieves all exam records from the database.
     *
     * @return array An array of Exam instances.
     */
    public static function findAll(): array
    {
        // Instantiate ExamRepository with a DatabaseInterface instance
        $repository = new ExamRepository(Database::getInstance());
        return $repository->findAll();
    }

    /**
     * Finds an exam by validated slug.
     *
     * @param string $slug The slug to validate and search for.
     * @return Exam|null The found exam instance or null if not found.
     */
    public static function findByValidatedSlug(string $slug): ?Exam
    {
        if (!Validation::validateSlug($slug)) {
            return null;
        }

        $repository = new ExamRepository(Database::getInstance());
        return $repository->findBy('slug', $slug);
    }
}
