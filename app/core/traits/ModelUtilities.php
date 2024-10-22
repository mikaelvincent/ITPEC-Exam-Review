<?php

namespace App\Core\Traits;

use App\Core\Database;
use App\Core\Validation;
use App\Core\Logger;

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
     * Saves the current model instance to the database.
     *
     * @return bool True on success, false otherwise.
     */
    public function save(): bool
    {
        $errors = $this->validate();
        if (!empty($errors)) {
            $logger = Logger::getInstance();
            foreach ($errors as $error) {
                $logger->error("Validation Error: " . $error);
            }
            return false;
        }

        $db = Database::getInstance();
        $attributes = $this->getAttributes();
        $primaryKeyValue = $this->attributes[$this->primaryKey] ?? null;

        if ($primaryKeyValue !== null) {
            // Update existing record
            $setClause = implode(", ", array_map(fn($col) => "{$col} = :{$col}", array_keys($attributes)));
            $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :{$this->primaryKey}";
        } else {
            // Insert new record
            $columns = implode(", ", array_keys($attributes));
            $params = implode(", ", array_map(fn($col) => ":{$col}", array_keys($attributes)));
            $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$params})";
        }

        try {
            $db->execute($sql, $attributes);
            if ($primaryKeyValue === null) {
                $this->attributes[$this->primaryKey] = (int) $db->getLastInsertId();
            }
            return true;
        } catch (\PDOException $e) {
            $logger = Logger::getInstance();
            $logger->error("Database Error on saving model: " . $e->getMessage());
            return false;
        }
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
