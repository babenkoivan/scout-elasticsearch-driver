<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Payloads\IndexPayload;
use ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;

class ElasticIndexDropCommand extends Command
{
    use RequiresIndexConfiguratorArgument;

    /**
     * {@inheritdoc}
     */
    protected $name = 'elastic:drop-index';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Drop an Elasticsearch index';

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $configurator = $this->getIndexConfigurator();

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
