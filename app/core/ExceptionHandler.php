<?php

namespace App\Core;

use Throwable;

/**
 * Manages uncaught exceptions and errors, centralizing logging and error handling.
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
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
    }

    /**
     * Handles uncaught exceptions by logging and displaying a generic error page.
     *
     * @param Throwable $exception The exception that was thrown.
     * @return void
     */
    public static function handleException(Throwable $exception): void
    {
        self::logException($exception);

        $code = $exception->getCode();
        http_response_code($code >= 400 && $code < 600 ? $code : 500);
        $errorTitle = ErrorHelper::getErrorTitle($code);

        $breadcrumbs = [
            [
                'title' => 'Home',
                'path' => Application::$app->request->getBasePath() ?: '/',
            ],
            [
                'title' => $errorTitle,
                'path' => '',
            ],
        ];

        $message = 'An unexpected error has occurred. Please try again later.';

        echo Application::$app !== null
            ? Application::$app->router->renderView('_error', [
                'message' => $message,
                'code' => $code,
                'breadcrumbs' => $breadcrumbs,
                'errorTitle' => $errorTitle,
            ])
            : "<h1>" . htmlspecialchars($errorTitle) . "</h1><p>{$message}</p>";
    }

    /**
     * Handles PHP errors by converting them to exceptions without exposing details.
     *
     * @param int $errno The level of the error raised.
     * @param string $errstr The error message.
     * @param string $errfile The filename that the error was raised in.
     * @param int $errline The line number the error was raised at.
     * @return bool Always returns true to prevent PHP internal error handler.
     * @throws \ErrorException
     */
    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool {
        $exception = new \ErrorException('An internal error occurred.', 0, $errno, $errfile, $errline);
        self::logException($exception);
        throw $exception;
    }

    /**
     * Logs exception details using the Logger.
     *
     * @param Throwable $exception The exception to log.
     * @return void
     */
    protected static function logException(Throwable $exception): void
    {
        $logger = Logger::getInstance();
        $requestDetails = '';

        if (isset(Application::$app->request)) {
            $request = Application::$app->request;
            $requestDetails = sprintf(
                'URI: %s | Method: %s | Query Params: %s',
                $request->getUri(),
                $request->getMethod(),
                json_encode($request->getQueryParams())
            );
        } else {
            $requestDetails = 'Request details not available.';
        }

        $logMessage = sprintf(
            'Exception: %s | Code: %s | File: %s:%s | Trace: %s | %s',
            $exception->getMessage(),
            $exception->getCode(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString(),
            $requestDetails
        );

        $logger->error($logMessage);
    }
}
