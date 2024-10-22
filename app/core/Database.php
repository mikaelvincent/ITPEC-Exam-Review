<?php

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use App\Core\Interfaces\DatabaseInterface;
use App\Core\Interfaces\LoggerInterface;

/**
 * Database class handles the connection to the database using PDO.
 * Implements Singleton pattern.
 */
class Database implements DatabaseInterface
{
    /**
     * The PDO instance.
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Singleton instance of the Database class.
     *
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * Logger instance for logging database activities.
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * The threshold in seconds to consider a query as slow.
     *
     * @var float
     */
    private float $slowQueryThreshold;

    /**
     * Private constructor to prevent direct instantiation.
     * Initializes the PDO connection using environment variables.
     *
     * @param LoggerInterface $logger Logger instance for logging.
     * @throws PDOException if the connection fails.
     */
    private function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->slowQueryThreshold = 1.0; // 1 second

        $host = $_ENV["DB_HOST"] ?? "127.0.0.1";
        $port = $_ENV["DB_PORT"] ?? "3306";
        $dbname = $_ENV["DB_NAME"] ?? "itpec_exam_review";
        $user = $_ENV["DB_USER"] ?? "root";
        $pass = $_ENV["DB_PASS"] ?? "";

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            $this->logger->error("Database connection failed: " . $e->getMessage());
            throw new PDOException("Database connection failed.");
        }
    }

    /**
     * Retrieves the singleton instance of the Database class.
     *
     * @return DatabaseInterface The singleton instance.
     */
    public static function getInstance(): DatabaseInterface
    {
        if (self::$instance === null) {
            // Ensure logger is available when creating the instance.
            $logger = Logger::getInstance();
            self::$instance = new self($logger);
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
    public function query(string $sql, array $params = []): PDOStatement
    {
        $startTime = microtime(true);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $executionTime = microtime(true) - $startTime;

            if ($executionTime > $this->slowQueryThreshold) {
                $this->logger->info(sprintf(
                    "Slow Query: %.4f seconds",
                    $executionTime
                ));
            }

            return $stmt;
        } catch (PDOException $e) {
            $originatingFunction = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]["function"] ?? "unknown";
            $this->logger->error(sprintf(
                "Database Query Error in %s: %s",
                $originatingFunction,
                $e->getMessage()
            ));
            throw new PDOException("An error occurred while executing the database query.");
        }
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
