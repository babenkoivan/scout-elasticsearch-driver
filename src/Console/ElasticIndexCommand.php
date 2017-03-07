<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use ScoutElastic\IndexConfigurator;
use Symfony\Component\Console\Input\InputArgument;

abstract class ElasticIndexCommand extends Command
{
    /**
     * @return IndexConfigurator
     */
    protected function getConfigurator()
    {
        $configurator = trim($this->argument('index-configurator'));
        return (new $configurator);
    }

    protected function getArguments()
    {
        return [
            ['index-configurator', InputArgument::REQUIRED, 'The index configurator class'],
        ];
    }
}