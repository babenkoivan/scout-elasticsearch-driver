<?php

namespace ScoutElastic\Console;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class SearchableModelMakeCommand extends ModelMakeCommand
{
    protected $name = 'make:searchable-model';

    protected $description = 'Create a new searchable model';

    public function getStub()
    {
        return __DIR__.'/stubs/searchable_model.stub';
    }

    protected function getOptions()
    {
        $options = parent::getOptions();

        $options[] = ['index-configurator', 'i', InputOption::VALUE_REQUIRED, 'Specify the index configurator for the model'];

        return $options;
    }

    protected function getConfiguratorOption()
    {
        return trim($this->option('index-configurator'));
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $configurator = $this->getConfiguratorOption();
        $stub = str_replace('DummyConfigurator', $configurator ? "{$configurator}::class" : "''", $stub);

        return $stub;
    }
}
