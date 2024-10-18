<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;

/**
 * User model represents the `user` table in the database.
 */
class User extends Model
{
    use Relationships;

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
        return $this->getRelatedModels(UserProgress::class, "id");
    }
}
