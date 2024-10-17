<?php

namespace App\Core;

/**
 * Request class encapsulates HTTP request data.
 */
class Request
{
    /**
     * The base path of the application.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Request constructor.
     *
     * Initializes the base path.
     */
    public function __construct()
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $this->basePath = rtrim(str_replace('index.php', '', $scriptName), '/');
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Retrieves the URI of the request, excluding the base path.
     *
     * @return string
     */
    public function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = strtok($uri, '?'); // Remove query string

        // Remove the base path from the URI
        if (strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }

        return $uri ?: '/';
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

        if ($this->getMethod() === 'GET') {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if ($this->getMethod() === 'POST') {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $body;
    }
}
