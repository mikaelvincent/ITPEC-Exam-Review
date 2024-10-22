<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;
use App\Core\Validation;

/**
 * User model represents users in the system, primarily identified by UID.
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
     * Validates user attributes.
     *
     * Ensures required fields are present and adhere to expected formats.
     *
     * @return array Validation errors, empty if none.
     */
    public function validate(): array
    {
        $errors = [];

        // Validate UID
        if (empty($this->uid)) {
            $errors[] = "UID is required.";
        } elseif (!Validation::validatePattern('/^[a-f0-9\-]{36}$/', $this->uid)) {
            $errors[] = "UID format is invalid.";
        }

        return $errors;
    }

    /**
     * Finds a user by their UID.
     *
     * @param string $uid UID to search for.
     * @return User|null User instance or null if not found.
     */
    public static function findByUid(string $uid): ?User
    {
        return self::findBy("uid", $uid);
    }

    /**
     * Retrieves progress records associated with the user.
     *
     * @return array Array of UserProgress instances.
     */
    public function getProgress(): array
    {
        return $this->getRelatedModels(UserProgress::class, "user_id");
    }
}
