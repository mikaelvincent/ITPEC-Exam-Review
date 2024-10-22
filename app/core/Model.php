<?php

namespace App\Core;

use App\Core\Traits\ModelUtilities;

/**
 * Abstract base class for all models, providing common database interactions.
 */
abstract class Model
{
    use ModelUtilities;

    /**
     * Primary key field name.
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Associated database table name.
     *
     * @var string
     */
    protected string $table;

    /**
     * Model attributes corresponding to table columns.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Stores validation errors.
     *
     * @var array
     */
    protected array $validationErrors = [];

    /**
     * Magic getter for model attributes.
     *
     * @param string $name Attribute name.
     * @return mixed|null Attribute value or null if not set.
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Magic setter for model attributes.
     *
     * @param string $name Attribute name.
     * @param mixed $value Attribute value.
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Checks if an attribute is set.
     *
     * @param string $name Attribute name.
     * @return bool True if set, false otherwise.
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Retrieves a record by primary key.
     *
     * @param int $id Primary key value.
     * @return static|null Model instance or null if not found.
     */
    public static function find(int $id): ?self
    {
        $instance = new static();
        $db = Database::getInstance();
        $sql = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id LIMIT 1";
        $data = $db->fetch($sql, ['id' => $id]);

        if ($data) {
            $instance->attributes = $data;
            return $instance;
        }

        return null;
    }

    /**
     * Retrieves a single record by a specific column and value.
     *
     * @param string $column Column name.
     * @param mixed $value Value to match.
     * @return static|null Model instance or null if not found.
     */
    public static function findBy(string $column, $value): ?self
    {
        $instance = new static();
        $db = Database::getInstance();
        $sql = "SELECT * FROM {$instance->table} WHERE {$column} = :value LIMIT 1";
        $data = $db->fetch($sql, ['value' => $value]);

        if ($data) {
            $instance->attributes = $data;
            return $instance;
        }

        return null;
    }

    /**
     * Retrieves all records from the associated table.
     *
     * @return array Array of model instances.
     */
    public static function findAll(): array
    {
        $instance = new static();
        $db = Database::getInstance();
        $sql = "SELECT * FROM {$instance->table}";
        $rows = $db->fetchAll($sql);

        $models = [];
        foreach ($rows as $row) {
            $model = new static();
            $model->attributes = $row;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Retrieves all records matching a specific column and value.
     *
     * @param string $column Column name.
     * @param mixed $value Value to match.
     * @return array Array of model instances.
     */
    public static function findAllBy(string $column, $value): array
    {
        $instance = new static();
        $db = Database::getInstance();
        $sql = "SELECT * FROM {$instance->table} WHERE {$column} = :value";
        $rows = $db->fetchAll($sql, ['value' => $value]);

        $models = [];
        foreach ($rows as $row) {
            $model = new static();
            $model->attributes = $row;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Deletes the current model instance from the database.
     *
     * @return bool True on success, false otherwise.
     */
    public function delete(): bool
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }

        $db = Database::getInstance();
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";

        try {
            $db->execute($sql, ["id" => $this->attributes[$this->primaryKey]]);
            return true;
        } catch (PDOException $e) {
            $logger = Logger::getInstance();
            $logger->error(
                "Database Error on deleting model: " . $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Validates model attributes.
     *
     * Should be overridden in child classes to implement specific validations.
     *
     * @return array Validation errors, empty if none.
     */
    public function validate(): array
    {
        return [];
    }
}
