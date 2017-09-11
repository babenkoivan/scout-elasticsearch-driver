<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use ScoutElastic\Console\Features\requiresIndexConfiguratorArgument;
use ScoutElastic\Facades\ElasticClient;
use Exception;
use ScoutElastic\Payloads\IndexPayload;

class ElasticIndexUpdateCommand extends Command
{
    use requiresIndexConfiguratorArgument;

    protected $name = 'elastic:update-index';

    protected $description = 'Update settings and mappings of an Elasticsearch index';

    public function handle()
    {
        if (!$configurator = $this->getIndexConfigurator()) {
            return;
        }

        $indexPayload = (new IndexPayload($configurator))->get();

        $indices = ElasticClient::indices();

        if (!$indices->exists($indexPayload)) {
            $this->error(sprintf(
                'Index %s doesn\'t exist',
                $configurator->getName()
            ));

            return;
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
}