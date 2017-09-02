<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use ScoutElastic\Console\Features\requiresIndexConfiguratorArgument;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Payloads\IndexPayload;

class ElasticIndexDropCommand extends Command
{
    use requiresIndexConfiguratorArgument;

    protected $name = 'elastic:drop-index';

    protected $description = 'Drop an Elasticsearch index';

    public function fire()
    {
        if (!$configurator = $this->getIndexConfigurator()) {
            return;
        }

        $payload = (new IndexPayload($configurator))
            ->get();

        ElasticClient::indices()
            ->delete($payload);

        $this->info(sprintf(
            'The index %s was deleted!',
            $configurator->getName()
        ));
    }
}