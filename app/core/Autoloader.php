<?php

namespace App\Core;

/**
 * Autoloader class responsible for automatically loading PHP classes.
 */
class Autoloader
{
    /**
     * Registers the autoload method with SPL autoload stack.
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register([self::class, "autoload"]);
    }

    /**
     * Autoloads the requested class by mapping namespaces to directory paths.
     *
     * @param string $class The fully-qualified class name.
     * @return void
     */
    private static function autoload(string $class): void
    {
        $prefix = "App\\";
        $baseDir = __DIR__ . "/../";

        // Check if the class uses the specified namespace prefix.
        if (strpos($class, $prefix) !== 0) {
            return;
        }

        // Remove the namespace prefix from the class name.
        $relativeClass = substr($class, strlen($prefix));

        // Replace namespace separators with directory separators in the relative class name.
        $file = $baseDir . str_replace("\\", "/", $relativeClass) . ".php";

        // Include the class file if it exists.
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
