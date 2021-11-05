<?php

namespace LBHounslow\ApiClient\Session;

interface SessionInterface
{
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name);

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null);

    /**
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value);
}