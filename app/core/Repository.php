<?php

namespace App\Core;

use App\Core\Interfaces\DatabaseInterface;

/**
 * Repository class centralizes database interactions for models.
 */
abstract class Repository
{
    /**
     * Database interface instance.
     *
     * @var DatabaseInterface
     */
    protected DatabaseInterface $db;

    /**
     * Associated model class name.
     *
     * @var string
     */
    protected string $modelClass;

    /**
     * Associated database table name.
     *
     * @var string
     */
    protected string $table;

    /**
     * Primary key field name.
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Repository constructor.
     *
     * @param DatabaseInterface $db
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
        $this->setModelClass();
        $this->setTable();
    }

    /**
     * Sets the associated model class name.
     *
     * @return void
     */
    abstract protected function setModelClass(): void;

    /**
     * Sets the associated table name.
     *
     * @return void
     */
    abstract protected function setTable(): void;

    /**
     * Finds a record by primary key.
     *
     * @param int $id
     * @return Model|null
     */
    public function find(int $id): ?Model
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $data = $this->db->fetch($sql, ['id' => $id]);

        return $this->createModel($data);
    }

    /**
     * Finds a single record by a specific column and value.
     *
     * @param string $column
     * @param mixed $value
     * @return Model|null
     */
    public function findBy(string $column, $value): ?Model
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        $data = $this->db->fetch($sql, ['value' => $value]);

        return $this->createModel($data);
    }

    /**
     * Finds all records.
     *
     * @return array
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $rows = $this->db->fetchAll($sql);

        return $this->createModels($rows);
    }

    /**
     * Finds all records matching a specific column and value.
     *
     * @param string $column
     * @param mixed $value
     * @return array
     */
    public function findAllBy(string $column, $value): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        $rows = $this->db->fetchAll($sql, ['value' => $value]);

        return $this->createModels($rows);
    }

    /**
     * Inserts a new record into the database.
     *
     * @param Model $model
     * @return bool
     */
    public function insert(Model $model): bool
    {
        $attributes = $model->getAttributes();
        $columns = implode(", ", array_keys($attributes));
        $params = implode(", ", array_map(fn($col) => ":{$col}", array_keys($attributes)));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$params})";

        try {
            $this->db->execute($sql, $attributes);
            $model->{$this->primaryKey} = (int) $this->db->getLastInsertId();
            return true;
        } catch (\PDOException $e) {
            // Log error if necessary
            return false;
        }
    }

    /**
     * Updates an existing record in the database.
     *
     * @param Model $model
     * @return bool
     */
    public function update(Model $model): bool
    {
        $attributes = $model->getAttributes();
        $setClause = implode(", ", array_map(fn($col) => "{$col} = :{$col}", array_keys($attributes)));
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :{$this->primaryKey}";

        try {
            $this->db->execute($sql, $attributes);
            return true;
        } catch (\PDOException $e) {
            // Log error if necessary
            return false;
        }
    }

    /**
     * Deletes a record from the database.
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";

        try {
            $this->db->execute($sql, ['id' => $model->{$this->primaryKey}]);
            return true;
        } catch (\PDOException $e) {
            // Log error if necessary
            return false;
        }
    }

    /**
     * Creates a model instance from data.
     *
     * @param array|null $data
     * @return Model|null
     */
    protected function createModel(?array $data): ?Model
    {
        if ($data) {
            $model = new $this->modelClass();
            $model->setAttributes($data);
            return $model;
        }
        return null;
    }

    /**
     * Creates multiple model instances from data.
     *
     * @param array $rows
     * @return array
     */
    protected function createModels(array $rows): array
    {
        $models = [];
        foreach ($rows as $row) {
            $model = $this->createModel($row);
            if ($model) {
                $models[] = $model;
            }
        }
        return $models;
    }
}
