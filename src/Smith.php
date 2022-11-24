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

        foreach ([self::INDEX_MESSAGES, self::INDEX_FILTER, self::INDEX_OPERATOR] as $k) {
            $_SESSION[$k] ??= [];
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




    // IS-54-16 : and produces a weapon for its purpose
    public function addMessage(string $level, string $message, array $context = []): void
    {
        if (!isset($_SESSION[self::INDEX_MESSAGES][$level])) {
            $_SESSION[self::INDEX_MESSAGES][$level] = [];
        }

        $_SESSION[self::INDEX_MESSAGES][$level][] = [$message, $context];
    }

    public function messages(string $level = null)
    {
        if (is_null($level)) {
            return $_SESSION[self::INDEX_MESSAGES];
        }

        return $_SESSION[self::INDEX_MESSAGES][$level] ?? null;
    }

    public function resetMessages(string $level = null): void
    {
        $this->reset(self::INDEX_MESSAGES, $level);
    }




    public function addRuntimeFilters(array $filters): void
    {
        $_SESSION[self::INDEX_FILTER] = array_merge($_SESSION[self::INDEX_FILTER], $filters);
    }

    public function hasFilter(string $filter_name): bool
    {
        return isset($_SESSION[self::INDEX_FILTER][$filter_name])
        && strlen('' . $_SESSION[self::INDEX_FILTER][$filter_name]) > 0;
    }

    public function addFilter(string $filter_name, string $value): void
    {
        $_SESSION[self::INDEX_FILTER][$filter_name] = $value;
    }

    public function filters(string $filter_name = null, string $value = null): ?string
    {
        if (is_null($filter_name)) {
            return $_SESSION[self::INDEX_FILTER];
        }

        if (!is_null($value)) {
            $this->addFilter($filter_name, $value);
        }

        return $_SESSION[self::INDEX_FILTER][$filter_name] ?? null;
    }

    public function resetFilters(string $filter_name = null): void
    {
        $this->reset(self::INDEX_FILTER, $filter_name);
    }

    public function operatorId(string $setter = null): ?string
    {
        if (!is_null($setter)) {
            $_SESSION[self::INDEX_OPERATOR] = ['id' => $setter, 'set_on' => time()];
        }

        return $_SESSION[self::INDEX_OPERATOR]['id'] ?? null;
    }

    public function operatorCheckinDate(): string
    {
        return $_SESSION[self::INDEX_OPERATOR]['set_on'] ?? null;
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

    private function reset(string $index, string $part = null): void
    {
        if (is_null($part)) {
            $_SESSION[$index] = [];
        } else {
            unset($_SESSION[$index][$part]);
        }
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
