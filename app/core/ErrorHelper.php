<?php

namespace App\Core;

/**
 * ErrorHelper provides utility functions for error handling.
 */
class ErrorHelper
{
    /**
     * Retrieves a human-readable error title based on the status code.
     *
     * @param int $code
     * @return string
     */
    public static function getErrorTitle(int $code): string
    {
        $titles = [
            404 => "404 Not Found",
            500 => "500 Internal Server Error",
            401 => "401 Unauthorized",
        ];

        return $titles[$code] ?? "Error";
    }
}
