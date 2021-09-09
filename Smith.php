<?php

namespace HexMakina\StateAgent;

use HexMakina\BlackBox\StateAgentInterface;

class Smith implements StateAgentInterface
{

    private static $instance = null;

    // IS-54-16 : Behold, I have created the smith who blows the fire of coals
    // $options : https://www.php.net/manual/fr/session.configuration.php
    public static function getInstance($options = []): StateAgentInterface
    {
        if (is_null(self::$instance)) {
            self::$instance = new Smith($options);
        }

        return self::$instance;
    }

    private function __construct($options = [])
    {
        switch(session_status())
        {
          case PHP_SESSION_DISABLED:
            throw new \UnexpectedValueException(__CLASS__.'::PHP_SESSION_DISABLED');

          case PHP_SESSION_NONE:
            session_name($options['session_name'] ?? StateAgentInterface::DEFAULT_SESSION_NAME);
            unset($options['session_name']);
            session_start($options); // https://www.php.net/manual/fr/function.session-start.php
          break;
        }

        if (!isset($_SESSION[self::INDEX_MESSAGES])) {
            $_SESSION[self::INDEX_MESSAGES] = [];
        }

        if (!isset($_SESSION[self::INDEX_FILTER])) {
            $_SESSION[self::INDEX_FILTER] = [];
        }

        if (!isset($_SESSION[self::INDEX_OPERATOR])) {
            $_SESSION[self::INDEX_OPERATOR] = [];
        }
    }



    // camelCase wrapper for setcookie, coherent with getCookie
    public function setCookie($name, $value = "", $expires_in = 365 * 24 * 60 * 60, $path = "/", $domain = "", $secure = false, $httponly = false): bool
    {
        return setcookie($name, $value, time() + $expires_in, $path, $domain, $secure, $httponly);
    }

    // returns the value stored or null
    public function getCookie($name)
    {
        return $_COOKIE[$name] ?? null;
    }




    // IS-54-16 : and produces a weapon for its purpose
    public function addMessage($level, $message, $context = [])
    {
        if (!isset($_SESSION[self::INDEX_MESSAGES][$level])) {
            $_SESSION[self::INDEX_MESSAGES][$level] = [];
        }

        $_SESSION[self::INDEX_MESSAGES][$level][] = [$message, $context];
    }

    public function messages($level = null)
    {
        if (is_null($level)) {
            return $_SESSION[self::INDEX_MESSAGES];
        }

        return $_SESSION[self::INDEX_MESSAGES][$level] ?? null;
    }

    public function resetMessages($level = null)
    {
        $this->reset(self::INDEX_MESSAGES, $level);
    }




    public function addRuntimeFilters($filters)
    {
        $_SESSION[self::INDEX_FILTER] = array_merge($_SESSION[self::INDEX_FILTER], $filters);
    }

    public function hasFilter($filter_name): bool
    {
        return isset($_SESSION[self::INDEX_FILTER][$filter_name]) && strlen('' . $_SESSION[self::INDEX_FILTER][$filter_name]) > 0;
    }

    public function addFilter($filter_name, $value)
    {
        $_SESSION[self::INDEX_FILTER][$filter_name] = $value;
    }

    public function filters($filter_name = null, $value = null)
    {
        if (is_null($filter_name)) {
            return $_SESSION[self::INDEX_FILTER];
        }

        if (!is_null($value)) {
            $this->addFilter($filter_name, $value);
        }

        return $_SESSION[self::INDEX_FILTER][$filter_name] ?? null;
    }

    public function resetFilters($filter_name = null)
    {
        $this->reset(self::INDEX_FILTER, $filter_name);
    }


    public function operatorId($setter = null)
    {
        if (!is_null($setter)) {
            $_SESSION[self::INDEX_OPERATOR] = ['id' => $setter, 'set_on' => time()];
        }

        return $_SESSION[self::INDEX_OPERATOR]['id'] ?? null;
    }

    public function operatorCheckinDate()
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

    private function reset($index, $part = null)
    {
        if (is_null($part)) {
            $_SESSION[$index] = [];
        } else {
            unset($_SESSION[$index][$part]);
        }
    }
}
