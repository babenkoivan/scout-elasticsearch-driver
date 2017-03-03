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

    protected function buildMappingPayload()
    {
        $configurator = $this->getConfigurator();

        $defaultMapping = $configurator->getDefaultMapping();

        if (!$defaultMapping) {
            return null;
        }

        return array_merge(
            $this->buildBasePayload(),
            [
                'type' => '_default_',
                'body' => [
                    '_default_' => $defaultMapping
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

            if ($mappingPayload = $this->buildMappingPayload()) {
                $indices->putMapping($mappingPayload);
            }

            $indices->open($basePayload);
        } catch (Exception $exception) {
            $indices->open($basePayload);

            throw $exception;
        }

        $this->info(sprintf(
            'The index %s was updated!',
            $indexName
        ));
    }
}