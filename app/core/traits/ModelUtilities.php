<?php

namespace App\Core\Traits;

use App\Core\Validation;

/**
 * Provides utility methods for models to reduce code duplication.
 */
trait ModelUtilities
{
    /**
     * Finds a record by validated slug.
     *
     * @param string $slug The slug to search for.
     * @return static|null Model instance or null if not found or invalid slug.
     */
    public static function findByValidatedSlug(string $slug): ?self
    {
        if (!Validation::validateSlug($slug)) {
            return null;
        }
        return static::findBy('slug', $slug);
    }

    /**
     * Retrieves all attributes of the model.
     *
     * @return array Model attributes.
     */
    protected function getAttributes(): array
    {
        return $this->attributes;
    }
}
