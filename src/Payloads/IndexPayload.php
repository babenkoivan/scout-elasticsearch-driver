<?php

namespace ScoutElastic\Payloads;

use Exception;
use ScoutElastic\IndexConfigurator;

class IndexPayload
{
    protected $payload = [];

    protected $protectedKeys = [
        'index'
    ];

    protected $indexConfigurator;

    public function __construct(IndexConfigurator $indexConfigurator)
    {
        $this->indexConfigurator = $indexConfigurator;

        $this->payload['index'] = $indexConfigurator->getName();
    }

    public function useAlias($alias)
    {
        $aliasGetter = 'get'.ucfirst($alias).'Alias';

        if (!method_exists($this->indexConfigurator, $aliasGetter)) {
            throw new Exception(sprintf(
                'The index configurator %s doesn\'t have getter for the %s alias.',
                get_class($this->indexConfigurator),
                $alias
            ));
        }

        $this->payload['index'] = call_user_func([$this->indexConfigurator, $aliasGetter]);

        return $this;
    }

    public function set($key, $value)
    {
        if (!is_null($key) && !in_array($key, $this->protectedKeys)) {
            array_set($this->payload, $key, $value);
        }

        return $this;
    }

    public function setIfNotEmpty($key, $value)
    {
        if (empty($value)) {
            return $this;
        }

        return $this->set($key, $value);
    }

    public function get($key = null)
    {
        return array_get($this->payload, $key);
    }
}