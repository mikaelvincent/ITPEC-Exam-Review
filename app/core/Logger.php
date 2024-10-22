<?php

namespace App\Core;

use App\Core\Interfaces\LoggerInterface;

/**
 * Logger class centralizes logging activities across the application.
 * It handles different log levels and manages log file rotation.
 */
class Logger implements LoggerInterface
{
    /**
     * The singleton instance of the Logger.
     *
     * @var Logger|null
     */
    private static ?Logger $instance = null;

    /**
     * The path to the log file.
     *
     * @var string
     */
    private string $logFile;

    /**
     * The maximum size of a log file before rotation (in bytes).
     *
     * @var int
     */
    private int $maxFileSize;

    /**
     * Logger constructor.
     *
     * Initializes the log file path and maximum file size.
     */
    private function __construct()
    {
        $logDirectory = __DIR__ . "/../../logs";
        if (!is_dir($logDirectory)) {
            mkdir($logDirectory, 0755, true);
        }

        $this->logFile = $logDirectory . "/app.log";
        $this->maxFileSize = 5 * 1024 * 1024; // 5 MB

        // Ensure the log file exists to prevent filesize() errors
        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, "");
        }
    }

    /**
     * Retrieves the singleton instance of the Logger.
     *
     * @return Logger The Logger instance.
     */
    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    /**
     * Logs an informational message.
     *
     * @param string $message The message to log.
     * @return void
     */
    public function info(string $message): void
    {
        $this->writeLog("INFO", $message);
    }

    /**
     * Logs an error message.
     *
     * @param string $message The message to log.
     * @return void
     */
    public function error(string $message): void
    {
        $this->writeLog("ERROR", $message);
    }

    /**
     * Logs a warning message.
     *
     * @param string $message The message to log.
     * @return void
     */
    public function warning(string $message): void
    {
        $this->writeLog("WARNING", $message);
    }

    /**
     * Writes a log entry to the log file.
     *
     * @param string $level The log level (e.g., INFO, ERROR).
     * @param string $message The log message.
     * @return void
     */
    private function writeLog(string $level, string $message): void
    {
        // Ensure the log file exists before attempting to get its size
        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, "");
        }

        if (filesize($this->logFile) >= $this->maxFileSize) {
            $this->rotateLog();
        }

        $timestamp = date("Y-m-d H:i:s");
        $logEntry = "[{$timestamp}] [{$level}] {$message}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rotates the current log file when it exceeds the maximum size.
     *
     * @return void
     */
    private function rotateLog(): void
    {
        $timestamp = date("Ymd_His");
        $rotatedFile = $this->logFile . "." . $timestamp;
        rename($this->logFile, $rotatedFile);

        // Create a new empty log file after rotation
        file_put_contents($this->logFile, "");
    }
}
