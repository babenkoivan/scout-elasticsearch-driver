<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class SearchableModelMakeCommand extends GeneratorCommand
{
    protected $name = 'make:searchable-model';

    protected $description = 'Create a new searchable model';

    protected $type = 'Model';

    public function getStub()
    {
        return __DIR__.'/stubs/searchable_model.stub';
    }

    protected function getOptions()
    {
        return [
            ['configurator', 'c', InputOption::VALUE_REQUIRED, 'Determine an index configurator for the model'],
        ];
    }

    protected function getConfiguratorOption()
    {
        return trim($this->option('configurator'));
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $configurator = $this->getConfiguratorOption();
        $stub = str_replace('DummyConfigurator', $configurator ? "{$configurator}::class" : "''", $stub);

        return $stub;
    }
}
