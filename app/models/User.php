<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;

/**
 * User model represents the `user` table in the database.
 * It accommodates both registered and unregistered users.
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
     * @return array An array of validation errors, empty if none.
     */
    public function validate(): array
    {
        $errors = [];

        // Validate UID for unregistered users
        if (!$this->isRegistered() && empty($this->uid)) {
            $errors[] = "UID is required for unregistered users.";
        }

        // If user is registered, validate username and password
        if ($this->isRegistered()) {
            if (empty($this->username)) {
                $errors[] = "Username is required for registered users.";
            } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $this->username)) {
                $errors[] =
                    "Username must be 3-20 characters and contain only letters, numbers, and underscores.";
            }

            if (empty($this->password_hash)) {
                $errors[] = "Password hash is required for registered users.";
            }
        }

        return $errors;
    }

    /**
     * Finds a user by UID.
     *
     * @param string $uid The UID to search for.
     * @return User|null The found User instance or null.
     */
    public static function findByUid(string $uid): ?User
    {
        $instance = new static();
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT * FROM {$instance->table} WHERE uid = :uid LIMIT 1";
        $data = $db->fetch($sql, ["uid" => $uid]);

        if ($data) {
            $instance->attributes = $data;
            return $instance;
        }

        return null;
    }

    /**
     * Determines if the user is registered.
     *
     * @return bool True if registered, false otherwise.
     */
    public function isRegistered(): bool
    {
        return !empty($this->username) && !empty($this->password_hash);
    }

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
