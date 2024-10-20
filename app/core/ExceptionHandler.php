<?php

namespace App\Core;

/**
 * ExceptionHandler class manages uncaught exceptions and errors.
 */
class ExceptionHandler
{
    /**
     * Registers the exception and error handlers.
     *
     * @return void
     */
    public static function register(): void
    {
        set_exception_handler([self::class, "handleException"]);
        set_error_handler([self::class, "handleError"]);
    }

    /**
     * Handles uncaught exceptions.
     *
     * @param \Throwable $exception The exception that was thrown.
     * @return void
     */
    public static function handleException(\Throwable $exception): void
    {
        // Log exception details using Logger
        $logger = Logger::getInstance();

        if (Application::$app !== null) {
            $request = Application::$app->request;
            $requestDetails = sprintf(
                "URI: %s | Method: %s | Query Params: %s",
                $request->getUri(),
                $request->getMethod(),
                json_encode($request->getQueryParams())
            );

            $logMessage = sprintf(
                "Exception: %s | Code: %s | File: %s:%s | Trace: %s | %s",
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString(),
                $requestDetails
            );
        } else {
            $logMessage = sprintf(
                "Exception: %s | Code: %s | File: %s:%s | Trace: %s",
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString()
            );
        }

        $logger->error($logMessage);

        http_response_code($exception->getCode() ?: 500);
        $errorTitle = self::getErrorTitle($exception->getCode());

        $breadcrumbs = [
            [
                "title" => "Home",
                "path" => Application::$app->request->getBasePath() ?: "/",
            ],
            [
                "title" => $errorTitle,
                "path" => "",
            ],
        ];

        echo Application::$app !== null
            ? Application::$app->router->renderView("_error", [
                "message" => $exception->getMessage(),
                "code" => $exception->getCode(),
                "breadcrumbs" => $breadcrumbs,
                "errorTitle" => $errorTitle,
            ])
            : "<h1>" .
                htmlspecialchars($errorTitle) .
                "</h1><p>An unexpected error has occurred.</p>";
    }

    /**
     * Handles PHP errors.
     *
     * @param int $errno The level of the error raised.
     * @param string $errstr The error message.
     * @param string $errfile The filename that the error was raised in.
     * @param int $errline The line number the error was raised at.
     * @return bool Always returns true to prevent PHP internal error handler.
     */
    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool {
        // Log error details using Logger
        $logger = Logger::getInstance();

        if (Application::$app !== null) {
            $request = Application::$app->request;
            $requestDetails = sprintf(
                "URI: %s | Method: %s | Query Params: %s",
                $request->getUri(),
                $request->getMethod(),
                json_encode($request->getQueryParams())
            );

            $logMessage = sprintf(
                "Error [{$errno}]: {$errstr} in {$errfile}:{$errline} | {$requestDetails}"
            );
        } else {
            $logMessage = sprintf(
                "Error [{$errno}]: {$errstr} in {$errfile}:{$errline}"
            );
        }

        $logger->error($logMessage);

        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Retrieves a human-readable error title based on the status code.
     *
     * @param int $code
     * @return string
     */
    protected static function getErrorTitle(int $code): string
    {
        $titles = [
            404 => "404 Not Found",
            500 => "500 Internal Server Error",
            401 => "401 Unauthorized",
        ];

        return $titles[$code] ?? "Error";
    }
}
