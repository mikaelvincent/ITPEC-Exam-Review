<?php

namespace App\Core;

/**
 * Validation class provides methods to sanitize and validate input data.
 */
class Validation
{
    /**
     * Sanitizes input data by encoding special characters.
     *
     * @param string $data The input data to sanitize.
     * @return string The sanitized data.
     */
    public static function sanitize(string $data): string
    {
        return htmlspecialchars($data, ENT_QUOTES, "UTF-8");
    }

    /**
     * Validates an email address.
     *
     * @param string $email The email address to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validates a string against a regular expression pattern.
     *
     * @param string $pattern The regex pattern to validate against.
     * @param string $string The string to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validatePattern(
        string $pattern,
        string $string
    ): bool {
        return preg_match($pattern, $string) === 1;
    }

    /**
     * Trims whitespace from the beginning and end of the string.
     *
     * @param string $data The string to trim.
     * @return string The trimmed string.
     */
    public static function trim(string $data): string
    {
        return trim($data);
    }

    /**
     * Validates the length of a string.
     *
     * @param string $data The string to validate.
     * @param int $min Minimum length.
     * @param int $max Maximum length.
     * @return bool True if within range, false otherwise.
     */
    public static function validateLength(
        string $data,
        int $min,
        int $max
    ): bool {
        $length = strlen($data);
        return $length >= $min && $length <= $max;
    }

    /**
     * Validates if the input is an integer.
     *
     * @param mixed $data The data to validate.
     * @return bool True if integer, false otherwise.
     */
    public static function validateInteger($data): bool
    {
        return filter_var($data, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validates a password against defined security criteria.
     *
     * @param string $password The password to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validatePassword(string $password): bool
    {
        // Criteria: at least 8 characters, includes letters and numbers
        return preg_match(
            '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
            $password
        ) === 1;
    }

    /**
     * Validates a slug against predefined criteria.
     *
     * @param string $slug The slug to validate.
     * @return bool True if valid, false otherwise.
     */
    public static function validateSlug(string $slug): bool
    {
        return self::validatePattern('/^[a-zA-Z0-9\-]{3,255}$/', $slug);
    }
}
