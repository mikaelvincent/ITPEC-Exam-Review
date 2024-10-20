<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;

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
        $uid = $_COOKIE[$uidCookieName] ?? null;
        $cookieExpiry = (int) ($_ENV["UID_COOKIE_EXPIRY"] ?? 315360000); // Default to 10 years

        if ($uid) {
            // Verify that UID exists in the user table
            $user = User::findByUid($uid);
            if ($user) {
                // Reset the cookie's expiry time
                setcookie($uidCookieName, $uid, [
                    "expires" => time() + $cookieExpiry,
                    "path" => "/",
                    "secure" => isset($_SERVER["HTTPS"]),
                    "httponly" => true,
                    "samesite" => "Lax",
                ]);
                $_SESSION["user_id"] = $user->id;
                return;
            }
            // UID is invalid; proceed to generate a new one
        }

        // Generate a new unique UID
        do {
            $newUid = bin2hex(random_bytes(16));
        } while (User::findByUid($newUid));

        // Insert new user with the generated UID
        $user = new User();
        $user->uid = $newUid;
        $user->save();

        // Set the UID cookie
        setcookie($uidCookieName, $newUid, [
            "expires" => time() + $cookieExpiry,
            "path" => "/",
            "secure" => isset($_SERVER["HTTPS"]),
            "httponly" => true,
            "samesite" => "Lax",
        ]);
        $_SESSION["user_id"] = $user->id;
    }
}
