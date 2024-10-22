<?php

namespace App\Core\Interfaces;

use PDOStatement;

/**
 * Interface for database operations to adhere to the Dependency Inversion Principle.
 */
interface DatabaseInterface
{
    /**
     * Retrieves the single instance of the DatabaseInterface implementation.
     *
     * @return DatabaseInterface
     */
    public static function getInstance(): DatabaseInterface;

    /**
     * Executes a query with optional parameters.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters for the SQL statement.
     * @return PDOStatement The resulting PDO statement.
     */
    public function query(string $sql, array $params = []): PDOStatement;

    /**
     * Fetches a single record from the database.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters for the SQL statement.
     * @return array|null The fetched record or null if not found.
     */
    public function fetch(string $sql, array $params = []): ?array;

    /**
     * Fetches all records matching the query.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters for the SQL statement.
     * @return array The fetched records.
     */
    public function fetchAll(string $sql, array $params = []): array;

    /**
     * Executes a non-select query (INSERT, UPDATE, DELETE).
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters for the SQL statement.
     * @return int The number of affected rows.
     */
    public function execute(string $sql, array $params = []): int;

    /**
     * Retrieves the last inserted ID.
     *
     * @return string The last insert ID.
     */
    public function getLastInsertId(): string;
}
