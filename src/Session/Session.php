<?php

namespace LBHounslow\ApiClient\Session;

class Session implements \ArrayAccess, SessionInterface
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
     * @inheritDoc
     */
    public function has(string $name)
    {
        return $this->offsetExists($name);
    }

    /**
     * @inheritDoc
     */
    public function get(string $name, $default = null)
    {
        return $this->offsetGet($name) ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function set(string $name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $_SESSION[self::NAMESPACE]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return isset($_SESSION[self::NAMESPACE][$offset])
            ? $_SESSION[self::NAMESPACE][$offset]
            : null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $_SESSION[self::NAMESPACE][$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        if (isset($_SESSION[self::NAMESPACE][$offset])) {
            unset($_SESSION[self::NAMESPACE][$offset]);
        }
    }

    private function createBag()
    {
        if (!isset($_SESSION[self::NAMESPACE])) {
            $_SESSION[self::NAMESPACE] = [];
        }
    }
}