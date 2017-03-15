<?php

namespace ScoutElastic\Console;

use ScoutElastic\Facades\ElasticClient;

class ElasticIndexCreateCommand extends ElasticIndexCommand
{
    protected $name = 'elastic:create-index';

    protected $description = 'Create an Elasticsearch index';

    protected function buildPayload()
    {
        $configurator = $this->getConfigurator();

        $body = [];

        if ($settings = $configurator->getSettings()) {
            $body['settings'] = $settings;
        }

        if ($defaultMappings = $configurator->getDefaultMapping()) {
            $body['mappings'] = ['_default_' => $defaultMappings];
        }

        $payload = $this->buildBasePayload();

        if ($body) {
            $payload['body'] = $body;
        }

        return $payload;
    }

    public function fire()
    {
        $configurator = $this->getConfigurator();

        ElasticClient::indices()
            ->create($this->buildPayload());

        $this->info(sprintf(
            'The index %s was created!',
            $configurator->getName()
        ));
    }
}