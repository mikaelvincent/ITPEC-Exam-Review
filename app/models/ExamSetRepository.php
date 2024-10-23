<?php

namespace App\Models;

use App\Core\Repository;
use App\Models\ExamSet;

/**
 * ExamSetRepository handles database interactions for the ExamSet model.
 */
class ExamSetRepository extends Repository
{
    /**
     * Sets the associated model class name.
     *
     * @return void
     */
    protected function setModelClass(): void
    {
        $this->modelClass = ExamSet::class;
    }

    /**
     * Sets the associated table name.
     *
     * @return void
     */
    protected function setTable(): void
    {
        $this->table = 'examset';
    }

    /**
     * Sets the list of allowed columns for querying.
     *
     * @return void
     */
    protected function setAllowedColumns(): void
    {
        $this->allowedColumns = ['id', 'exam_id', 'name', 'slug', 'created_at', 'updated_at'];
    }
}
