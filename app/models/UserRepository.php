<?php

namespace App\Models;

use App\Core\Repository;

/**
 * UserRepository handles database interactions for User model.
 */
class UserRepository extends Repository
{
    /**
     * Sets the associated model class name.
     *
     * @return void
     */
    protected function setModelClass(): void
    {
        $this->modelClass = User::class;
    }

    /**
     * Sets the associated table name.
     *
     * @return void
     */
    protected function setTable(): void
    {
        $this->table = 'user';
    }

    /**
     * Sets the list of allowed columns for querying.
     *
     * @return void
     */
    protected function setAllowedColumns(): void
    {
        $this->allowedColumns = ['id', 'uid', 'username', 'display_name', 'password_hash', 'created_at', 'updated_at'];
    }

    /**
     * Finds a user by UID.
     *
     * @param string $uid
     * @return User|null
     */
    public function findByUid(string $uid): ?User
    {
        return $this->findBy('uid', $uid);
    }
}
