<?php

use ScoutElastic\Tests\Config;

if (! function_exists('config')) {
    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        return Config::get($key, $default);
    }
}

include __DIR__.'/../vendor/autoload.php';
