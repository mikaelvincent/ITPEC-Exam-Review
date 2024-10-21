<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;

/**
 * User model represents users in the system, including both registered and unregistered users.
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

        // Validate UID for unregistered users
        if (!$this->isRegistered()) {
            if (empty($this->uid)) {
                $errors[] = "UID is required for unregistered users.";
            } elseif (
                !Validation::validatePattern('/^[a-f0-9-]{36}$/', $this->uid)
            ) {
                $errors[] = "UID format is invalid.";
            }
        }

        // If user is registered, validate username and password
        if ($this->isRegistered()) {
            if (empty($this->username)) {
                $errors[] = "Username is required for registered users.";
            } elseif (
                !Validation::validatePattern(
                    '/^[a-zA-Z0-9_]{3,20}$/',
                    $this->username
                )
            ) {
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
     * Hashes the user's password using a secure algorithm.
     *
     * @param string $password The plain text password.
     * @return bool True on success, false otherwise.
     */
    public function setPassword(string $password): bool
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if ($hashedPassword === false) {
            return false;
        }

        $this->password_hash = $hashedPassword;
        return true;
    }

    /**
     * Verifies a plain text password against the stored hash.
     *
     * @param string $password The plain text password.
     * @return bool True if the password matches, false otherwise.
     */
    public function verifyPassword(string $password): bool
    {
        if (empty($this->password_hash)) {
            return false;
        }

        return password_verify($password, $this->password_hash);
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
     * Determines if the user is registered based on the presence of username and password hash.
     *
     * @return bool True if registered, false otherwise.
     */
    public function isRegistered(): bool
    {
        return !empty($this->username) && !empty($this->password_hash);
    }

    /**
     * Retrieves progress records associated with the user.
     *
     * @return array Array of UserProgress instances.
     */
    public function getProgress(): array
    {
        return $this->getRelatedModels(UserProgress::class, "id");
    }
}
