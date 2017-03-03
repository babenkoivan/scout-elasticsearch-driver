<?php

namespace ScoutElastic\Console;

use ScoutElastic\Facades\ElasticClient;
use Exception;

class ElasticIndexUpdateCommand extends ElasticIndexCommand
{
    protected $name = 'elastic:update-index';

    protected $description = 'Update settings and mappings of an Elasticsearch index';

    protected function buildBasePayload()
    {
        $configurator = $this->getConfigurator();

        return [
            'index' => $configurator->getName()
        ];
    }

    protected function buildMappingsPayload()
    {
        $configurator = $this->getConfigurator();

        $defaultMappings = $configurator->getDefaultMapping();

        if (!$defaultMappings) {
            return null;
        }

        return array_merge(
            $this->buildBasePayload(),
            [
                'type' => '_default_',
                'body' => [
                    '_default_' => $defaultMappings
                ]
            ]
        );
    }

    protected function buildSettingsPayload()
    {
        $configurator = $this->getConfigurator();

        $settings = $configurator->getSettings();

        if (!$settings) {
            return null;
        }

        return array_merge(
            $this->buildBasePayload(),
            [
                'body' => [
                    'settings' => $settings
                ]
            ]
        );
    }

    public function fire()
    {
        $configurator = $this->getConfigurator();

        $indexName = $configurator->getName();
        $basePayload = $this->buildBasePayload();

        $indices = ElasticClient::indices();

        if (!$indices->exists($basePayload)) {
            $this->error(sprintf(
                'Index %s doesn\'t exist',
                $indexName
            ));

            return;
        }

        try {
            $indices->close($basePayload);

            if ($settingsPayload = $this->buildSettingsPayload()) {
                $indices->putSettings($settingsPayload);
            }

            if ($mappingsPayload = $this->buildMappingsPayload()) {
                $indices->putMapping($mappingsPayload);
            }

            $indices->open($basePayload);
        } catch (Exception $exception) {
            $indices->open($basePayload);

            throw $exception;
        }

        $this->info(sprintf(
            'Index %s was updated!',
            $indexName
        ));
    }
}