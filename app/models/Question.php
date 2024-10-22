<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;
use App\Core\Database;

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
        return $this->getRelatedModels(Answer::class, "id");
    }

    /**
     * Finds a question by exam set slug and question number.
     *
     * @param string $examSetSlug The slug of the exam set.
     * @param int $questionNumber The question number.
     * @return Question|null The found Question instance or null.
     */
    public static function findByExamSetSlugAndNumber(
        string $examSetSlug,
        int $questionNumber
    ): ?Question {
        $examSet = ExamSet::findBySlug($examSetSlug);
        if (!$examSet) {
            return null;
        }

        $db = Database::getInstance();
        $sql = "SELECT q.* FROM question q WHERE q.exam_set_id = :examSetId AND q.question_number = :questionNumber LIMIT 1";
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
