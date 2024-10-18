<?php

namespace App\Core;

use App\Core\Database;
use PDOException;

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
     * Saves the current model instance to the database.
     *
     * @return bool True on success, false otherwise.
     */
    public function save(): bool
    {
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
                $this->attributes[$this->primaryKey] = $db->fetch(
                    "SELECT LAST_INSERT_ID() as id"
                )["id"];
            }
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
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
            error_log($e->getMessage());
            return false;
        }
    }

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
}