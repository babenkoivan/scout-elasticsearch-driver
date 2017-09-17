<?php

namespace ScoutElastic\Console\Features;

use InvalidArgumentException;
use ScoutElastic\IndexConfigurator;
use Symfony\Component\Console\Input\InputArgument;

trait requiresIndexConfiguratorArgument
{
    /**
     * @return IndexConfigurator|null
     */
    protected function getIndexConfigurator()
    {
        $configuratorClass = trim($this->argument('index-configurator'));

        $configuratorInstance = new $configuratorClass;

        if (!($configuratorInstance instanceof IndexConfigurator)) {
            throw new InvalidArgumentException(sprintf(
                'The class %s must extend %s.',
                $configuratorClass,
                IndexConfigurator::class
            ));
        }

        return (new $configuratorClass);
    }

    protected function getArguments()
    {
        return [
            ['index-configurator', InputArgument::REQUIRED, 'The index configurator class'],
        ];
    }
}