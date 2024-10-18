<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database class handles the connection to the database using PDO.
 * Implements the Singleton pattern to ensure a single connection instance.
 */
class Database
{
    /**
     * The single instance of the Database.
     *
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * The PDO instance.
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Database constructor.
     *
     * Initializes the PDO connection using environment variables.
     *
     * @throws PDOException if the connection fails.
     */
    private function __construct()
    {
        $host = $_ENV["DB_HOST"] ?? "127.0.0.1";
        $port = $_ENV["DB_PORT"] ?? "3306";
        $dbname = $_ENV["DB_NAME"] ?? "itpec_exam_review";
        $user = $_ENV["DB_USER"] ?? "root";
        $pass = $_ENV["DB_PASS"] ?? "";

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            // Log the error message
            error_log($e->getMessage());
            throw new PDOException("Database connection failed.");
        }
    }

    /**
     * Retrieves the single instance of the Database.
     *
     * @return Database The Database instance.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Executes a query with optional parameters.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters for the SQL statement.
     * @return PDOStatement The resulting PDO statement.
     * @throws PDOException if the query fails.
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetches a single record from the database.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters for the SQL statement.
     * @return array|null The fetched record or null if not found.
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Fetches all records matching the query.
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters for the SQL statement.
     * @return array The fetched records.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Executes a non-select query (INSERT, UPDATE, DELETE).
     *
     * @param string $sql The SQL statement.
     * @param array $params The parameters for the SQL statement.
     * @return int The number of affected rows.
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
}
