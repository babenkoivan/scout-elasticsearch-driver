<?php

namespace ScoutElastic\Console;

use ScoutElastic\Facades\ElasticClient;

class ElasticIndexDropCommand extends ElasticIndexCommand
{
    protected $name = 'elastic:drop-index';

    protected $description = 'Drop an Elasticsearch index';

    public function fire()
    {
        $configurator = $this->getConfigurator();

        ElasticClient::indices()
            ->delete($this->buildBasePayload());

        $this->info(sprintf(
            'The index %s was deleted!',
            $configurator->getName()
        ));
    }
}