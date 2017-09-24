<?php

namespace ScoutElastic\Payloads;

use Exception;
use ScoutElastic\IndexConfigurator;

class IndexPayload extends RawPayload
{
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
        if (in_array($key, $this->protectedKeys)) {
            return $this;
        }

        return parent::set($key, $value);
    }
}