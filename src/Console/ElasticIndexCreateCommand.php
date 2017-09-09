<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use ScoutElastic\Console\Features\CompatibilityTrait;
use ScoutElastic\Console\Features\requiresIndexConfiguratorArgument;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Payloads\IndexPayload;

class ElasticIndexCreateCommand extends Command
{
    use requiresIndexConfiguratorArgument;
    use CompatibilityTrait;

    protected $name = 'elastic:create-index';

    protected $description = 'Create an Elasticsearch index';

    public function handleCommand()
    {
        if (!$configurator = $this->getIndexConfigurator()) {
            return;
        }

        $payload = (new IndexPayload($configurator))
            ->setIfNotEmpty('body.settings', $configurator->getSettings())
            ->setIfNotEmpty('body.mappings._default_', $configurator->getDefaultMapping())
            ->get();

        ElasticClient::indices()
            ->create($payload);

        $this->info(sprintf(
            'The index %s was created!',
            $configurator->getName()
        ));
    }
}