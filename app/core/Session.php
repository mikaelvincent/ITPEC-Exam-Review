<?php

namespace App\Core;

/**
 * Session class provides an abstraction layer for session management.
 */
class Session
{
    /**
     * Starts the session if not already started.
     *
     * @return void
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Sets a session variable.
     *
     * @param string $key The session key.
     * @param mixed $value The value to set.
     * @return void
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieves a session variable.
     *
     * @param string $key The session key.
     * @return mixed|null The value of the session variable or null if not set.
     */
    public static function get(string $key)
    {
        self::start();
        return $_SESSION[$key] ?? null;
    }

    /**
     * Unsets a session variable.
     *
     * @param string $key The session key.
     * @return void
     */
    public static function unset(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destroys the entire session.
     *
     * @return void
     */
    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            session_unset();
            session_destroy();
        }
    }
}
