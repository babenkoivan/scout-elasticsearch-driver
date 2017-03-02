<?php

namespace ScoutElastic\Console;

use ScoutElastic\Facades\ElasticClient;

class ElasticIndexCreateCommand extends ElasticIndexCommand
{
    protected $name = 'elastic:create-index';

    protected $description = 'Create an Elasticsearch index';

    public function fire()
    {
        $configurator = $this->getConfigurator();

        ElasticClient::indices()
            ->create($configurator->toArray());

        $this->info(sprintf(
            'Index %s was created!',
            $configurator->getName()
        ));
    }
}