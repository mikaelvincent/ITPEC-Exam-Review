<?php

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use App\Core\Interfaces\DatabaseInterface;

/**
 * Database class handles the connection to the database using PDO.
 * Implements the Singleton pattern to ensure a single connection instance.
 */
class Database implements DatabaseInterface
{
    /**
     * The single instance of the Database.
     *
     * @var DatabaseInterface|null
     */
    private static ?DatabaseInterface $instance = null;

    /**
     * The PDO instance.
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Logger instance for logging database activities.
     *
     * @var Logger
     */
    private Logger $logger;

    /**
     * The threshold in seconds to consider a query as slow.
     *
     * @var float
     */
    private float $slowQueryThreshold;

    /**
     * Database constructor.
     *
     * Initializes the PDO connection using environment variables.
     *
     * @throws PDOException if the connection fails.
     */
    private function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->slowQueryThreshold = 1.0; // 1 second

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
            // Log the error message with connection details
            $this->logger->error(
                "Database connection failed: " . $e->getMessage()
            );
            throw new PDOException("Database connection failed.");
        }
    }

    /**
     * Retrieves the single instance of the DatabaseInterface implementation.
     *
     * @return DatabaseInterface The DatabaseInterface instance.
     */
    public static function getInstance(): DatabaseInterface
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
     * @return \PDOStatement The resulting PDO statement.
     * @throws PDOException if the query fails.
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $startTime = microtime(true);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $executionTime = microtime(true) - $startTime;

            if ($executionTime > $this->slowQueryThreshold) {
                $this->logger->info(
                    sprintf(
                        "Slow Query: %.4f seconds | SQL: %s",
                        $executionTime,
                        $this->sanitizeSql($sql)
                    )
                );
            }

            return $stmt;
        } catch (PDOException $e) {
            // Log detailed error information using Logger
            $originatingFunction =
                debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1][
                    "function"
                ] ?? "unknown";
            $logMessage = sprintf(
                "Database Query Error in %s: %s | SQL: %s",
                $originatingFunction,
                $e->getMessage(),
                $this->sanitizeSql($sql)
            );
            $this->logger->error($logMessage);
            throw new PDOException(
                "An error occurred while executing the database query."
            );
        }
    }

    /**
     * Sanitizes SQL statements to remove sensitive information.
     *
     * @param string $sql The SQL statement.
     * @return string The sanitized SQL statement.
     */
    private function sanitizeSql(string $sql): string
    {
        // Remove potential sensitive data such as passwords
        return preg_replace(
            "/password_hash\s*=\s*:[a-zA-Z0-9_]+/",
            "password_hash=***",
            $sql
        );
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

    /**
     * Retrieves the last inserted ID.
     *
     * @return string The last insert ID.
     */
    public function getLastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
