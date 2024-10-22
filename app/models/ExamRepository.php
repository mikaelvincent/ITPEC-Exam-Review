<?php

namespace App\Models;

use App\Core\Repository;

/**
 * ExamRepository handles database interactions for the Exam model.
 */
class ExamRepository extends Repository
{
    /**
     * Sets the associated model class name.
     *
     * @return void
     */
    protected function setModelClass(): void
    {
        $this->modelClass = Exam::class;
    }

    /**
     * Sets the associated table name.
     *
     * @return void
     */
    protected function setTable(): void
    {
        $this->table = 'exam';
    }

    /**
     * Sets the list of allowed columns for querying.
     *
     * @return void
     */
    protected function setAllowedColumns(): void
    {
        $this->allowedColumns = ['id', 'name', 'slug', 'created_at', 'updated_at'];
    }
}
