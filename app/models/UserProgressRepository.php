<?php

namespace App\Models;

use App\Core\Repository;

/**
 * UserProgressRepository handles database interactions for the UserProgress model.
 */
class UserProgressRepository extends Repository
{
    /**
     * Sets the associated model class name.
     *
     * @return void
     */
    protected function setModelClass(): void
    {
        $this->modelClass = UserProgress::class;
    }

    /**
     * Sets the associated table name.
     *
     * @return void
     */
    protected function setTable(): void
    {
        $this->table = 'userprogress';
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
            'user_id',
            'selected_answer_id',
            'is_active',
            'attempted_at'
        ];
    }

    /**
     * Inserts a new UserProgress record into the database.
     *
     * @param UserProgress $userProgress
     * @return bool
     */
    public function insertUserProgress(UserProgress $userProgress): bool
    {
        return $this->insert($userProgress);
    }
}
