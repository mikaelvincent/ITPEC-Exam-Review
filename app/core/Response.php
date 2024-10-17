<?php

namespace App\Core;

/**
 * Response class manages HTTP responses.
 */
class Response
{
    /**
     * Sets the HTTP status code for the response.
     *
     * @param int $code
     * @return void
     */
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    /**
     * Redirects to a specified URL.
     *
     * @param string $url
     * @return void
     */
    public function redirect(string $url): void
    {
        header("Location: " . $url);
        exit();
    }
}
