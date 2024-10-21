<?php

namespace App\Core;

use App\Core\Database;
use PDOException;
use App\Core\Logger;

/**
 * Base Model class providing common database interaction functionalities.
 */
abstract class Model
{
    /**
     * The primary key field name.
     *
     * @var string
     */
    protected string $primaryKey = "id";

    /**
     * The table name associated with the model.
     *
     * @var string
     */
    protected string $table;

    /**
     * Attributes corresponding to table columns.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Array to store validation errors.
     *
     * @var array
     */
    protected array $validationErrors = [];

    /**
     * Magic getter to access attributes.
     *
     * @param string $name The attribute name.
     * @return mixed The attribute value.
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Magic setter to set attributes.
     *
     * @param string $name The attribute name.
     * @param mixed $value The attribute value.
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Magic method to check if an attribute is set.
     *
     * @param string $name The attribute name.
     * @return bool True if set, false otherwise.
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Finds a record by its primary key.
     *
     * @param int $id The primary key value.
     * @return static|null The found model instance or null.
     */
    public static function find(int $id): ?self
    {
        $instance = new static();
        $db = Database::getInstance();
        $sql = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id LIMIT 1";
        $data = $db->fetch($sql, ["id" => $id]);

        if ($data) {
            $instance->attributes = $data;
            return $instance;
        }

        return null;
    }

    /**
     * Retrieves all records from the table.
     *
     * @return array An array of model instances.
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
     * Finds all records matching a specific column and value.
     *
     * @param string $column The column name to filter by.
     * @param mixed $value The value to match.
     * @return array An array of model instances.
     */
    public static function findAllBy(string $column, $value): array
    {
        $instance = new static();
        $db = Database::getInstance();
        $sql = "SELECT * FROM {$instance->table} WHERE {$column} = :value";
        $rows = $db->fetchAll($sql, ["value" => $value]);

        $models = [];
        foreach ($rows as $row) {
            $model = new static();
            $model->attributes = $row;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Saves the current model instance to the database.
     *
     * Handles both registered and unregistered users based on the presence of specific attributes.
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

        if (isset($this->attributes[$this->primaryKey])) {
            // Update existing record
            $columns = array_keys($this->attributes);
            $columns = array_filter(
                $columns,
                fn($col) => $col !== $this->primaryKey
            );
            $setClause = implode(
                ", ",
                array_map(fn($col) => "{$col} = :{$col}", $columns)
            );
            $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :{$this->primaryKey}";
        } else {
            // Insert new record
            $columns = array_keys($this->attributes);
            $columnClause = implode(", ", $columns);
            $paramClause = implode(
                ", ",
                array_map(fn($col) => ":{$col}", $columns)
            );
            $sql = "INSERT INTO {$this->table} ({$columnClause}) VALUES ({$paramClause})";
        }

        try {
            $db->execute($sql, $this->attributes);
            if (!isset($this->attributes[$this->primaryKey])) {
                $this->attributes[
                    $this->primaryKey
                ] = (int) $db->getLastInsertId();
            }
            return true;
        } catch (PDOException $e) {
            $logger = Logger::getInstance();
            $logger->error(
                "Database Error on saving model: " . $e->getMessage()
            );
            return false;
        }
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
     * Override this method in child classes to implement specific validations.
     *
     * @return array An array of validation errors, empty if none.
     */
    public function validate(): array
    {
        return [];
    }

    /**
     * Defines a relationship to another model.
     *
     * @param string $relationshipType The type of relationship (e.g., 'hasOne', 'hasMany').
     * @param string $relatedModel The related model class name.
     * @param string $foreignKey The foreign key in the related model.
     * @return mixed
     */
    public function relationship(
        string $relationshipType,
        string $relatedModel,
        string $foreignKey
    ) {
        switch ($relationshipType) {
            case "hasOne":
                return $relatedModel::find($this->attributes[$foreignKey]);
            case "hasMany":
                return $relatedModel::findAllBy(
                    $foreignKey,
                    $this->attributes[$foreignKey]
                );
            default:
                return null;
        }
    }
}
