<?php

namespace ScoutElastic\Console\Features;

use ScoutElastic\IndexConfigurator;
use Symfony\Component\Console\Input\InputArgument;

trait requiresIndexConfiguratorArgument
{
    /**
     * @return IndexConfigurator
     */
    protected function getIndexConfigurator()
    {
        $configuratorClass = trim($this->argument('index-configurator'));

        $configuratorInstance = new $configuratorClass;

        if (!($configuratorInstance instanceof IndexConfigurator)) {
            $this->error(sprintf(
                'The class %s must extend %s.',
                $configuratorClass,
                IndexConfigurator::class
            ));

            return null;
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