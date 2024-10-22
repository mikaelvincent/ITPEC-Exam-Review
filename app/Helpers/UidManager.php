<?php

namespace App\Helpers;

use App\Core\Session;
use App\Core\Cookie;
use App\Core\Validation;
use App\Models\User;
use App\Models\UserRepository;
use App\Core\Interfaces\LoggerInterface;
use App\Core\Database;

/**
 * UidManager handles UID generation, validation, and cookie management.
 */
class UidManager
{
    /**
     * Logger instance for logging activities.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Session instance for session management.
     *
     * @var Session
     */
    protected Session $session;

    /**
     * Name of the UID cookie.
     *
     * @var string
     */
    protected string $uidCookieName;

    /**
     * UID cookie expiry time in seconds.
     *
     * @var int
     */
    protected int $cookieExpiry;

    /**
     * UserRepository instance.
     *
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * Constructor initializes the UidManager.
     *
     * @param LoggerInterface $logger Logger instance.
     * @param Session $session Session instance.
     * @param string $uidCookieName Name of the UID cookie.
     * @param int $cookieExpiry UID cookie expiry time in seconds.
     */
    public function __construct(LoggerInterface $logger, Session $session, string $uidCookieName = 'uid', int $cookieExpiry = 315360000)
    {
        $this->logger = $logger;
        $this->session = $session;
        $this->uidCookieName = $uidCookieName;
        $this->cookieExpiry = $cookieExpiry;
        $this->userRepository = new UserRepository(Database::getInstance());
    }

    /**
     * Handles UID management including validation and regeneration.
     *
     * @return void
     */
    public function handleUid(): void
    {
        $uid = Cookie::get($this->uidCookieName);

        if ($uid && $this->isValidUid($uid)) {
            $user = $this->userRepository->findByUid($uid);
            if ($user) {
                $this->refreshUidCookie($uid);
                $this->session->set('user_id', $user->id);
                $this->logger->info("Valid UID found. User ID: {$user->id}.");
                return;
            }
            $this->logger->warning("UID does not correspond to any user: {$uid}");
        } elseif ($uid) {
            $this->logger->warning("Invalid UID format detected: {$uid}");
        }

        $this->generateNewUid();
    }

    /**
     * Generates a new UID and creates a user.
     *
     * @return void
     */
    protected function generateNewUid(): void
    {
        $this->logger->info("Generating a new UID for the user.");

        $newUid = $this->generateUuidV4();

        $user = new User();
        $user->uid = $newUid;
        if (empty($user->validate())) {
            $this->userRepository->insert($user);
            $this->refreshUidCookie($newUid);
            $this->session->set('user_id', $user->id);
            $this->logger->info("New UID generated and user created. User ID: {$user->id}.");
        } else {
            $this->logger->error("Failed to create a new user with UID: {$newUid}.");
        }
    }

    /**
     * Generates a UUID version 4.
     *
     * @return string The generated UUID.
     */
    protected function generateUuidV4(): string
    {
        $data = random_bytes(16);

        // Set version to 0100
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Validates if a given UID is a valid UUID format.
     *
     * @param string $uid The UID to validate.
     * @return bool True if valid, false otherwise.
     */
    protected function isValidUid(string $uid): bool
    {
        return (bool) preg_match('/^[a-f0-9\-]{36}$/', $uid);
    }

    /**
     * Refreshes the UID cookie by resetting its expiration.
     *
     * @param string $uid The UID to set in the cookie.
     * @return void
     */
    protected function refreshUidCookie(string $uid): void
    {
        Cookie::set($this->uidCookieName, $uid, [
            'expires' => time() + $this->cookieExpiry,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
