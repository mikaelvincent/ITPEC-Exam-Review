<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Validation;

/**
 * User model represents users in the system, primarily identified by UID.
 */
class User extends Model
{
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
}
