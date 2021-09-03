<?php

namespace HexMakina\StateAgent;

interface StateAgentInterface
{
    const INDEX_MESSAGES = 'user_messages';
    const INDEX_FILTER = 'filter';
    const INDEX_OPERATOR = 'operator';

    // camelCase wrapper for setcookie, coherent with getCookie
    public function setCookie($name, $value = "", $expires_in = 365 * 24 * 60 * 60, $path = "/", $domain = "", $secure = false, $httponly = false): bool;

    // returns the value stored or null
    public function getCookie($name);

    public function addMessage($level, $message, $context = []);
    public function messages($level = null);
    public function resetMessages($level = null);


    public function addRuntimeFilters($filters);
    public function hasFilter($filter_name): bool;

    public function addFilter($filter_name, $value);
    public function filters($filter_name = null, $value = null);
    public function resetFilters($filter_name = null);

    public function operatorId($setter = null);
    public function operatorCheckinDate();

    public function destroy(): bool;
}
