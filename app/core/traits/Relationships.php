<?php

namespace App\Core\Traits;

use App\Core\Model;

/**
 * Relationships trait provides methods to define and retrieve related models.
 */
trait Relationships
{
    /**
     * Retrieves a related model based on the foreign key.
     *
     * @param string $relatedModel The related model class name.
     * @param string $foreignKey The foreign key attribute.
     * @return Model|null The related model instance or null.
     */
    protected function getRelatedModel(
        string $relatedModel,
        string $foreignKey
    ): ?Model {
        return $relatedModel::find($this->{$foreignKey});
    }

    /**
     * Retrieves all related models based on the foreign key.
     *
     * @param string $relatedModel The related model class name.
     * @param string $foreignKey The foreign key attribute.
     * @return array An array of related model instances.
     */
    protected function getRelatedModels(
        string $relatedModel,
        string $foreignKey
    ): array {
        return $relatedModel::findAllBy($foreignKey, $this->{$foreignKey});
    }
}
