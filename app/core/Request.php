<?php

namespace App\Core;

/**
 * Request class encapsulates HTTP request data.
 */
class Request
{
    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $_SERVER["REQUEST_METHOD"] ?? "GET";
    }

    /**
     * Retrieves the URI of the request.
     *
     * @return string
     */
    public function getUri(): string
    {
        $uri = $_SERVER["REQUEST_URI"] ?? "/";
        $position = strpos($uri, "?");

        if ($position === false) {
            return $uri;
        }

        return substr($uri, 0, $position);
    }

    /**
     * Retrieves sanitized GET parameters.
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        return $_GET;
    }

    /**
     * Retrieves sanitized POST parameters.
     *
     * @return array
     */
    public function getBody(): array
    {
        $body = [];

        if ($this->getMethod() === "GET") {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(
                    INPUT_GET,
                    $key,
                    FILTER_SANITIZE_SPECIAL_CHARS
                );
            }
        }

        if ($this->getMethod() === "POST") {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(
                    INPUT_POST,
                    $key,
                    FILTER_SANITIZE_SPECIAL_CHARS
                );
            }
        }

        return $body;
    }
}
