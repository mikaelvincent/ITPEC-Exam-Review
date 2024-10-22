<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\UidManager;
use App\Core\Logger;

/**
 * UidCookieMiddleware manages UID cookies for unregistered users.
 */
class UidCookieMiddleware
{
    /**
     * Executes the middleware logic for UID cookie management.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function __invoke(Request $request, Response $response): void
    {
        $logger = Logger::getInstance();
        $uidCookieName = 'uid';
        $cookieExpiry = (int) ($_ENV['UID_COOKIE_EXPIRY'] ?? 315360000); // Default to 10 years

        $uidManager = new UidManager($logger, $uidCookieName, $cookieExpiry);
        $uidManager->handleUid();
    }
}
