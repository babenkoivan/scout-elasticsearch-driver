<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class IndexConfiguratorMakeCommand extends GeneratorCommand
{
    protected $name = 'make:index-configurator';

    protected $description = 'Create a new Elasticsearch index configurator';

    protected $type = 'Configurator';

    public function getStub()
    {
        return __DIR__.'/stubs/index_configurator.stub';
    }
}
