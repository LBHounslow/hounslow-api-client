<?php

namespace App\Session;

class Session
{
    const NAMESPACE = 'HounslowApiClient';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->createBag();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return array_key_exists($name, $_SESSION[self::NAMESPACE]);
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        if (isset($_SESSION[self::NAMESPACE][$name])) {
            return $_SESSION[self::NAMESPACE][$name];
        }
        return $default;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value)
    {
        $_SESSION[self::NAMESPACE][$name] = $value;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $_SESSION[self::NAMESPACE];
    }

    /**
     * @param string $name
     */
    public function remove(string $name)
    {
        if (isset($_SESSION[self::NAMESPACE][$name])) {
            unset($_SESSION[self::NAMESPACE][$name]);
        }
    }

    public function clear()
    {
        if (isset($_SESSION[self::NAMESPACE])) {
            unset($_SESSION[self::NAMESPACE]);
        }
    }

    private function createBag()
    {
        if (!isset($_SESSION[self::NAMESPACE])) {
            $_SESSION[self::NAMESPACE] = [];
        }
    }
}