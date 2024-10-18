<?php

namespace App\Core;

/**
 * EnvLoader class responsible for loading environment variables from a `.env` file.
 */
class EnvLoader
{
    /**
     * Loads environment variables from the specified `.env` file.
     *
     * @param string $path The path to the `.env` file.
     * @return void
     */
    public static function load(
        string $path = __DIR__ . "/../../config/.env"
    ): void {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), "#") === 0) {
                continue;
            }
            list($name, $value) = explode("=", $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv(sprintf("%s=%s", $name, $value));
            }
        }
    }
}
