<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

abstract class ElasticIndexCommand extends Command
{
    protected function getConfigurator()
    {
        $configurator = $this->argument('configurator');
        return (new $configurator);
    }

    protected function getArguments()
    {
        return [
            ['configurator', InputArgument::REQUIRED, 'The index configurator class.'],
        ];
    }
}