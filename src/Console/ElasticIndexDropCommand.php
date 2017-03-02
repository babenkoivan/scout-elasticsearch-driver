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

        $name = $configurator->getName();

        ElasticClient::indices()
            ->delete(['index' => $name]);

        $this->info(sprintf(
            'Index %s was deleted!',
            $name
        ));
    }
}