<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Traits\Relationships;
use App\Core\Validation;

/**
 * PageView model represents the `pageview` table in the database.
 */
class PageView extends Model
{
    use Relationships;

    /**
     * The table associated with the PageView model.
     *
     * @var string
     */
    protected string $table = "pageview";

    /**
     * Validates PageView model's attributes.
     *
     * @return array Validation errors, empty if none.
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->user_id) || !Validation::validateInteger($this->user_id)) {
            $errors[] = "Invalid user ID.";
        }

        if (empty($this->page_url)) {
            $errors[] = "Page URL is required.";
        }

        if (!empty($this->ip_address) && !filter_var($this->ip_address, FILTER_VALIDATE_IP)) {
            $errors[] = "Invalid IP address format.";
        }

        return $errors;
    }

    /**
     * Gets the user associated with the page view.
     *
     * @return User|null The associated User instance or null.
     */
    public function getUser(): ?User
    {
        return $this->getRelatedModel(User::class, "user_id");
    }
}
