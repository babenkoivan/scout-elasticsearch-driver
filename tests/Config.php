<?php

namespace ScoutElastic\Tests;

class Config
{
    /**
     * @var array
     */
    private static $values = [];

    /**
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        array_set(static::$values, $key, $value);
    }

    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get($key = null, $default = null)
    {
        return array_get(static::$values, $key, $default);
    }

    /**
     * @param array $values
     */
    public static function reset(array $values = [])
    {
        static::$values = $values;

        foreach ($values as $key => $value) {
            static::set($key, $value);
        }
    }
}