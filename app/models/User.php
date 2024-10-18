<?php

namespace App\Models;

use App\Core\Model;

/**
 * User model represents the `user` table in the database.
 */
class User extends Model
{
    /**
     * The table associated with the User model.
     *
     * @var string
     */
    protected string $table = "user";

    /**
     * Gets the user progress records associated with the user.
     *
     * @return array An array of UserProgress instances.
     */
    public function getProgress(): array
    {
        return UserProgress::findAllByUserId($this->id);
    }
}
