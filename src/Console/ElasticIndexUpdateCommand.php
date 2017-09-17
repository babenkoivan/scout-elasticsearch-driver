<?php

namespace ScoutElastic\Console;

use Exception;
use LogicException;
use Illuminate\Console\Command;
use ScoutElastic\Console\Features\requiresIndexConfiguratorArgument;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Payloads\IndexPayload;

class ElasticIndexUpdateCommand extends Command
{
    use requiresIndexConfiguratorArgument;

    protected $name = 'elastic:update-index';

    protected $description = 'Update settings and mappings of an Elasticsearch index';

    protected function updateIndex()
    {
        $configurator = $this->getIndexConfigurator();

        $indexPayload = (new IndexPayload($configurator))->get();

        $indices = ElasticClient::indices();

        if (!$indices->exists($indexPayload)) {
            throw new LogicException(sprintf(
                'Index %s doesn\'t exist',
                $configurator->getName()
            ));
        }

        try {
            $indices->close($indexPayload);

            if ($settings = $configurator->getSettings()) {
                $indexSettingsPayload = (new IndexPayload($configurator))
                    ->set('body.settings', $settings)
                    ->get();

                $indices->putSettings($indexSettingsPayload);
            }

            if ($defaultMapping = $configurator->getDefaultMapping()) {
                $indexMappingPayload = (new IndexPayload($configurator))
                    ->set('type', '_default_')
                    ->set('body._default_', $defaultMapping)
                    ->get();

                $indices->putMapping($indexMappingPayload);
            }

            $indices->open($indexPayload);
        } catch (Exception $exception) {
            $indices->open($indexPayload);

            throw $exception;
        }

        $this->info(sprintf(
            'The index %s was updated!',
            $configurator->getName()
        ));
    }

    protected function createWriteAlias()
    {
        $configurator = $this->getIndexConfigurator();

        if (!method_exists($configurator, 'getWriteAlias')) {
            return;
        }

        $indices = ElasticClient::indices();

        if ($indices->existsAlias(['name' => $configurator->getWriteAlias()])) {
            return;
        }

        $payload = (new IndexPayload($configurator))
            ->set('name', $configurator->getWriteAlias())
            ->get();

        $indices->putAlias($payload);

        $this->info(sprintf(
            'The %s alias for the %s index was created!',
            $configurator->getWriteAlias(),
            $configurator->getName()
        ));
    }

    public function handle()
    {
        $this->updateIndex();

        $this->createWriteAlias();
    }
}