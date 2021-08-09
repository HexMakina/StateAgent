<?php

namespace HexMakina\Smith;

class Smith
{
  private $index_filter = 'filter';
  private $index_operator = 'operator';

  // IS-54-16 : Behold, I have created the smith who blows the fire of coals
  // $options : https://www.php.net/manual/fr/session.configuration.php
  public function __construct($options)
  {
    if(isset($options['session_name']))
    {
      session_name($options['session_name']);
      unset($options['session_name']);
    }

    session_start($options); // https://www.php.net/manual/fr/function.session-start.php
  }

  // IS-54-16 : and produces a weapon for its purpose

  public function add_runtime_filters($filters)
  {
    $_SESSION[$this->index_filter] = array_merge($_SESSION[$this->index_filter] ?? [], $filters);
  }

  public function has_filter($filter_name) : bool
  {
    return isset($_SESSION[$this->index_filter][$filter_name]) && strlen(''.$_SESSION[$this->index_filter][$filter_name]) > 0;
  }

  public function filters($filter_name=null, $value=null)
  {
    if(is_null($filter_name))
      return $_SESSION[$this->index_filter];

    if(!is_null($value))
      $_SESSION[$this->index_filter][$filter_name] = $value;

    return $_SESSION[$this->index_filter][$filter_name] ?? null;
  }

  public function reset_filters($filter_name=null)
  {
    if(is_null($filter_name))
      $_SESSION[$this->index_filter]=[];
    else
      unset($_SESSION[$this->index_filter][$filter_name]);
  }

  public function operator_id($setter = null)
  {
    if(!is_null($setter))
      $_SESSION[$this->index_operator] = ['id' => $setter, 'set_on' => time()];

    return $_SESSION[$this->index_operator]['id'] ?? null;
  }

  public function operator_started_on()
  {
    return $_SESSION[$this->index_operator]['set_on'] ?? null;
  }

  // IS-54-16 : I have also created the ravager to destroy
  public function destroy() : bool
  {

    if(ini_get("session.use_cookies"))
    {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
      );
    }
    return session_destroy();
  }
}
