<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\UidManager;
use App\Core\Logger;
use App\Core\Application;

/**
 * UidCookieMiddleware manages UID cookies for unregistered users.
 */
class UidCookieMiddleware
{
    /**
     * Executes the middleware logic for UID cookie management.
     *
     * @param Request $request The current request instance.
     * @param Response $response The current response instance.
     * @param callable $next The next middleware or controller action.
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $logger = Logger::getInstance();
        $uidCookieName = 'uid';
        $cookieExpiry = (int) ($_ENV['UID_COOKIE_EXPIRY'] ?? 315360000); // Default to 10 years

        $session = Application::$app->session;
        $uidManager = new UidManager($logger, $session, $uidCookieName, $cookieExpiry);
        $uidManager->handleUid();

        // Proceed to the next middleware or controller
        return $next($request, $response);
    }
}
