<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;

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
     * Finds an exam by its name.
     *
     * @param string $name The name of the exam.
     * @return Exam|null The found Exam instance or null.
     */
    public static function findByName(string $name): ?Exam
    {
        $instance = new static();
        $db = Database::getInstance();
        $sql = "SELECT * FROM {$instance->table} WHERE name = :name LIMIT 1";
        $data = $db->fetch($sql, ["name" => $name]);

        if ($data) {
            $instance->attributes = $data;
            return $instance;
        }

        return null;
    }

    /**
     * Gets the exam sets associated with the exam.
     *
     * @return array An array of ExamSet instances.
     */
    public function getExamSets(): array
    {
        return $this->getRelatedModels(ExamSet::class, "exam_id");
    }
}
