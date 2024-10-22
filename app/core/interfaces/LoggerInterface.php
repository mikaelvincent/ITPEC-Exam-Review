<?php

namespace App\Core\Interfaces;

/**
 * Interface for logging operations to adhere to the Dependency Inversion Principle.
 */
interface LoggerInterface
{
    /**
     * Retrieves the singleton instance of the LoggerInterface implementation.
     *
     * @return LoggerInterface
     */
    public static function getInstance(): LoggerInterface;

    /**
     * Logs an informational message.
     *
     * @param string $message The message to log.
     * @return void
     */
    public function info(string $message): void;

    /**
     * Logs an error message.
     *
     * @param string $message The message to log.
     * @return void
     */
    public function error(string $message): void;

    /**
     * Logs a warning message.
     *
     * @param string $message The message to log.
     * @return void
     */
    public function warning(string $message): void;
}
