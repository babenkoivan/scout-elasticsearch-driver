<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Migratable;
use ScoutElastic\Payloads\RawPayload;

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
        $indexName = $this->resolveIndexName($configurator);

        $payload = (new RawPayload)
            ->set('index', $indexName)
            ->get();

        ElasticClient::indices()
            ->delete($payload);

        $this->info(sprintf(
            'The index %s was deleted!',
            $indexName
        ));
    }

    /**
     * @param  \ScoutElastic\IndexConfigurator  $configurator
     * @return string
     */
    protected function resolveIndexName($configurator)
    {
        if (in_array(Migratable::class, class_uses_recursive($configurator))) {
            $payload = (new RawPayload)
                ->set('name', $configurator->getWriteAlias())
                ->get();

            $aliases = ElasticClient::indices()
                ->getAlias($payload);

            return key($aliases);
        } else {
            return $configurator->getName();
        }
    }
}
