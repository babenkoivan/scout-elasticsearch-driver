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

        $name = $configurator->getName();
        $body = [];

        if ($settings = $this->getSettings()) {
            $body['settings'] = $settings;
        }

        if ($mappings = $this->getMappings()) {
            $body['mappings'] = $mappings;
        }

        ElasticClient::indices()
            ->create([
                'index' => $name,
                'body' => $body
            ]);

        $this->info(sprintf(
            'Index %s was created!',
            $name
        ));
    }
}