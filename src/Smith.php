<?php

namespace HexMakina\StateAgent;

use HexMakina\BlackBox\StateAgentInterface;

class Smith implements StateAgentInterface
{

    private static ?StateAgentInterface $instance = null;

    // IS-54-16 : Behold, I have created the smith who blows the fire of coals
    // $options : https://www.php.net/manual/fr/session.configuration.php
    public static function getInstance(array $options = []): StateAgentInterface
    {
        if (is_null(self::$instance)) {
            self::$instance = new Smith($options);
           
        }

        return self::$instance;
    }

    public function start(): string
    {
        if (self::hasNoSession()) {
            session_start();
        }

        return session_name();
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    private function __construct(array $options = [])
    {
        if (self::sessionsAreDisabled()) {
            throw new \UnexpectedValueException(__CLASS__ . '::PHP_SESSION_DISABLED');
        }

        if (self::hasNoSession()) {
            session_name($options['session_name'] ?? StateAgentInterface::DEFAULT_SESSION_NAME);
            unset($options['session_name']);
            session_start($options); // https://www.php.net/manual/fr/function.session-start.php
        }

    }

    // camelCase wrapper for setcookie, coherent with getCookie
    public function setCookie(
        string $name,
        string $value = "",
        int $expires_in = 365 * 24 * 60 * 60,
        string $path = "/",
        string $domain = "",
        bool $secure = false,
        bool $httponly = false
    ): bool {
        return setcookie($name, $value, time() + $expires_in, $path, $domain, $secure, $httponly);
    }

    // returns the value stored or null
    public function getCookie(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }

    // IS-54-16 : I have also created the ravager to destroy
    public function destroy(): bool
    {

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        return session_destroy();
    }


    private static function sessionsAreDisabled(): bool
    {
        return session_status() === PHP_SESSION_DISABLED;
    }

    private static function hasNoSession(): bool
    {
        return session_status() === PHP_SESSION_NONE;
    }
}
