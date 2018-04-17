<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class IndexConfiguratorMakeCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'make:index-configurator';

    /**
     * @var string
     */
    protected $description = 'Create a new Elasticsearch index configurator';

    /**
     * @var string
     */
    protected $type = 'Configurator';

    /**
     * @inheritdoc
     */
    public function getStub()
    {
        return __DIR__ . '/stubs/index_configurator.stub';
    }
}
