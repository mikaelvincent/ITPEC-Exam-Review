<?php

namespace App\Core;

/**
 * Cookie class provides an abstraction layer for cookie management.
 */
class Cookie
{
    /**
     * Sets a cookie with the specified parameters.
     *
     * @param string $name The name of the cookie.
     * @param string $value The value of the cookie.
     * @param array $options Additional options for the cookie.
     * @return bool True on success, false otherwise.
     */
    public static function set(
        string $name,
        string $value,
        array $options = []
    ): bool {
        $defaults = [
            "expires" => time() + 31536000, // 1 year
            "path" => "/",
            "domain" => "",
            "secure" => isset($_SERVER["HTTPS"]),
            "httponly" => true,
            "samesite" => "Lax",
        ];

        $options = array_merge($defaults, $options);

        return setcookie($name, $value, [
            "expires" => $options["expires"],
            "path" => $options["path"],
            "domain" => $options["domain"],
            "secure" => $options["secure"],
            "httponly" => $options["httponly"],
            "samesite" => $options["samesite"],
        ]);
    }

    /**
     * Retrieves the value of a cookie.
     *
     * @param string $name The name of the cookie.
     * @return string|null The value of the cookie or null if not set.
     */
    public static function get(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }

    /**
     * Deletes a cookie by setting its expiration time in the past.
     *
     * @param string $name The name of the cookie.
     * @return bool True on success, false otherwise.
     */
    public static function delete(string $name): bool
    {
        return self::set($name, "", ["expires" => time() - 3600]);
    }
}
