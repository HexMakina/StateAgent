<?php

namespace HexMakina\Smith;

class Smith
{
    const REPORTING_USER = 'user_messages';
    const INDEX_FILTER = 'filter';
    const INDEX_OPERATOR = 'operator';

  // IS-54-16 : Behold, I have created the smith who blows the fire of coals
  // $options : https://www.php.net/manual/fr/session.configuration.php
    public function __construct($options)
    {
        if (isset($options['session_name'])) {
            session_name($options['session_name']);
            unset($options['session_name']);
        }

        session_start($options); // https://www.php.net/manual/fr/function.session-start.php
    }


  // IS-54-16 : and produces a weapon for its purpose
    public function add_message($level, $message, $context = [])
    {
        if (!isset($_SESSION[self::REPORTING_USER])) {
            $_SESSION[self::REPORTING_USER] = [];
        }

        if (!isset($_SESSION[self::REPORTING_USER][$level])) {
            $_SESSION[self::REPORTING_USER][$level] = [];
        }

        $_SESSION[self::REPORTING_USER][$level][] = [$message, $context];
    }

    public function messages($level = null)
    {
        if (is_null($level)) {
            return $_SESSION[self::REPORTING_USER];
        }

        return $_SESSION[self::REPORTING_USER][$level] ?? null;
    }

    public function reset_messages($level = null)
    {
        $this->reset(self::REPORTING_USER, $level);
    }


    public function add_runtime_filters($filters)
    {
        $_SESSION[self::INDEX_FILTER] = array_merge($_SESSION[self::INDEX_FILTER] ?? [], $filters);
    }

    public function has_filter($filter_name): bool
    {
        return isset($_SESSION[self::INDEX_FILTER][$filter_name]) && strlen('' . $_SESSION[self::INDEX_FILTER][$filter_name]) > 0;
    }

    public function filters($filter_name = null, $value = null)
    {
        if (is_null($filter_name)) {
            return $_SESSION[self::INDEX_FILTER];
        }

        if (!is_null($value)) {
            $_SESSION[self::INDEX_FILTER][$filter_name] = $value;
        }

        return $_SESSION[self::INDEX_FILTER][$filter_name] ?? null;
    }

    public function reset_filters($filter_name = null)
    {
        $this->reset(self::INDEX_FILTER, $filter_name);
    }

    public function operator_id($setter = null)
    {
        if (!is_null($setter)) {
            $_SESSION[self::INDEX_OPERATOR] = ['id' => $setter, 'set_on' => time()];
        }

        return $_SESSION[self::INDEX_OPERATOR]['id'] ?? null;
    }

    public function operator_started_on()
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
