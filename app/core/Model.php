<?php

namespace App\Core;

use App\Core\Interfaces\DatabaseInterface;
use App\Core\Interfaces\LoggerInterface;

/**
 * Abstract base class for all models, providing common database interactions.
 */
abstract class Model
{
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
     * Database interface instance for dependency injection.
     *
     * @var DatabaseInterface
     */
    protected DatabaseInterface $db;

    /**
     * Logger interface instance for dependency injection.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Constructor with optional dependency injection.
     *
     * @param DatabaseInterface|null $db
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ?DatabaseInterface $db = null,
        ?LoggerInterface $logger = null
    ) {
        $this->db = $db ?? Database::getInstance();
        $this->logger = $logger ?? Logger::getInstance();
    }

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
        $sql = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id LIMIT 1";
        $data = $instance->db->fetch($sql, ['id' => $id]);

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
        $sql = "SELECT * FROM {$instance->table} WHERE {$column} = :value LIMIT 1";
        $data = $instance->db->fetch($sql, ['value' => $value]);

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
        $sql = "SELECT * FROM {$instance->table}";
        $rows = $instance->db->fetchAll($sql);

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
        $sql = "SELECT * FROM {$instance->table} WHERE {$column} = :value";
        $rows = $instance->db->fetchAll($sql, ['value' => $value]);

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

        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";

        try {
            $this->db->execute($sql, ["id" => $this->attributes[$this->primaryKey]]);
            return true;
        } catch (\PDOException $e) {
            $this->logger->error("Database Error on deleting model: " . $e->getMessage());
            return false;
        }
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
            foreach ($errors as $error) {
                $this->logger->error("Validation Error: " . $error);
            }
            return false;
        }

        $primaryKeyValue = $this->attributes[$this->primaryKey] ?? null;

        if ($primaryKeyValue !== null) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * Inserts a new record into the database.
     *
     * @return bool True on success, false otherwise.
     */
    protected function insert(): bool
    {
        $attributes = $this->getAttributes();

        $columns = implode(", ", array_keys($attributes));
        $params = implode(", ", array_map(fn($col) => ":{$col}", array_keys($attributes)));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$params})";

        try {
            $this->db->execute($sql, $attributes);
            $this->attributes[$this->primaryKey] = (int) $this->db->getLastInsertId();
            return true;
        } catch (\PDOException $e) {
            $this->logger->error("Database Error on inserting model: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an existing record in the database.
     *
     * @return bool True on success, false otherwise.
     */
    protected function update(): bool
    {
        $attributes = $this->getAttributes();

        $setClause = implode(", ", array_map(fn($col) => "{$col} = :{$col}", array_keys($attributes)));
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :{$this->primaryKey}";

        try {
            $this->db->execute($sql, $attributes);
            return true;
        } catch (\PDOException $e) {
            $this->logger->error("Database Error on updating model: " . $e->getMessage());
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

    /**
     * Returns the model attributes.
     *
     * @return array Model attributes.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

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
}
