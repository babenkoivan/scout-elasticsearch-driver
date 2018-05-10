<?php

namespace ScoutElastic\Payloads;

use Exception;
use ScoutElastic\IndexConfigurator;
use ScoutElastic\Payloads\Features\HasProtectedKeys;

class IndexPayload extends RawPayload
{
    use HasProtectedKeys;

    /**
     * @var array
     */
    protected $protectedKeys = [
        'index'
    ];

    /**
     * @var IndexConfigurator
     */
    protected $indexConfigurator;

    /**
     * @param IndexConfigurator $indexConfigurator
     */
    public function __construct(IndexConfigurator $indexConfigurator)
    {
        $this->indexConfigurator = $indexConfigurator;

        $this->payload['index'] = $indexConfigurator->getName();
    }

    /**
     * @param string $alias
     * @return $this
     * @throws Exception
     */
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
}