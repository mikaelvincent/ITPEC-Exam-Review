<?php

namespace App\Models;

use App\Core\Repository;

/**
 * AnswerRepository handles database interactions for the Answer model.
 */
class AnswerRepository extends Repository
{
    /**
     * Sets the associated model class name.
     *
     * @return void
     */
    protected function setModelClass(): void
    {
        $this->modelClass = Answer::class;
    }

    /**
     * Sets the associated table name.
     *
     * @return void
     */
    protected function setTable(): void
    {
        $this->table = 'answer';
    }

    /**
     * Sets the list of allowed columns for querying.
     *
     * @return void
     */
    protected function setAllowedColumns(): void
    {
        $this->allowedColumns = [
            'id',
            'question_id',
            'label',
            'content',
            'image_path',
            'is_correct',
            'created_at',
            'updated_at'
        ];
    }
}
