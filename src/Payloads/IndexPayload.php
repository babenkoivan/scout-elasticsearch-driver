<?php

namespace ScoutElastic\Payloads;

use Exception;
use ScoutElastic\IndexConfigurator;
use ScoutElastic\Payloads\Features\HasProtectedKeys;

class IndexPayload extends RawPayload
{
    use HasProtectedKeys;

    /**
     * The protected keys.
     *
     * @var array
     */
    protected $protectedKeys = [
        'index',
    ];

    /**
     * The index configurator.
     *
     * @var \ScoutElastic\IndexConfigurator
     */
    protected $indexConfigurator;

    /**
     * IndexPayload constructor.
     *
     * @param \ScoutElastic\IndexConfigurator $indexConfigurator
     * @return void
     */
    public function __construct(IndexConfigurator $indexConfigurator)
    {
        $this->indexConfigurator = $indexConfigurator;

        $this->payload['index'] = $indexConfigurator->getName();
    }

    /**
     * Use an alias.
     *
     * @param string $alias
     * @return $this
     * @throws \Exception
     */
    public function useAlias($alias)
    {
        $aliasGetter = 'get'.ucfirst($alias).'Alias';

        if (! method_exists($this->indexConfigurator, $aliasGetter)) {
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
