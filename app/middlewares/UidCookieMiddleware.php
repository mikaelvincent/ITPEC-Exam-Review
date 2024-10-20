<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Cookie;
use App\Models\User;
use App\Core\Logger;
use App\Core\Validation;

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
        $uidCookieName = "uid";
        $uid = Cookie::get($uidCookieName);
        $cookieExpiry = (int) ($_ENV["UID_COOKIE_EXPIRY"] ?? 315360000); // Default to 10 years
        $logger = Logger::getInstance();

        if ($uid) {
            // Validate UUID v4 format
            if (!Validation::validatePattern('/^[a-f0-9-]{36}$/', $uid)) {
                $logger->warning("Invalid UID format detected: {$uid}");
                $uid = null;
            } else {
                // Verify that UID exists in the user table
                $user = User::findByUid($uid);
                if ($user) {
                    // Reset the cookie's expiry time
                    Cookie::set($uidCookieName, $uid, [
                        "expires" => time() + $cookieExpiry,
                        "path" => "/",
                        "secure" => isset($_SERVER["HTTPS"]),
                        "httponly" => true,
                        "samesite" => "Lax",
                    ]);
                    Session::set("user_id", $user->id);

                    // Log successful UID validation
                    $logger->info("Valid UID found. User ID: {$user->id}.");
                    return;
                }
                // UID is invalid; proceed to generate a new one
                $logger->warning("UID does not correspond to any user: {$uid}");
            }
        }

        // Generate a new UID if none exists or the existing UID is invalid
        $logger->info("Generating a new UID for the user.");

        $newUid = $this->generateUuidV4();

        // Insert new user with the generated UID
        $user = new User();
        $user->uid = $newUid;
        if ($user->save()) {
            // Set the UID cookie with secure flags
            Cookie::set($uidCookieName, $newUid, [
                "expires" => time() + $cookieExpiry,
                "path" => "/",
                "secure" => isset($_SERVER["HTTPS"]),
                "httponly" => true,
                "samesite" => "Lax",
            ]);
            Session::set("user_id", $user->id);

            // Log UID generation and user creation
            $logger->info(
                "New UID generated and user created. User ID: {$user->id}."
            );
        } else {
            // Log failure to create user with validation errors
            $logger->error("Failed to create a new user with UID: {$newUid}.");
        }
    }

    /**
     * Generates a UUID version 4.
     *
     * @return string The generated UUID.
     */
    private function generateUuidV4(): string
    {
        $data = random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf("%s%s-%s-%s-%s-%s%s%s", str_split(bin2hex($data), 4));
    }
}
