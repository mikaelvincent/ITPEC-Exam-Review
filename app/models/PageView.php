<?php

namespace App\Models;

use App\Core\Model;

/**
 * PageView model represents the `pageview` table in the database.
 */
class PageView extends Model
{
    /**
     * The table associated with the PageView model.
     *
     * @var string
     */
    protected string $table = "pageview";

    /**
     * Gets the user associated with the page view.
     *
     * @return User|null The associated User instance or null.
     */
    public function getUser(): ?User
    {
        return User::find($this->user_id);
    }
}
