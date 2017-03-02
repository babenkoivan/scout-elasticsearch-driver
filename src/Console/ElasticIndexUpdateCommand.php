<?php

namespace ScoutElastic\Console;

use ScoutElastic\Facades\ElasticClient;
use Exception;

class ElasticIndexUpdateCommand extends ElasticIndexCommand
{
    protected $name = 'elastic:update-index';

    protected $description = 'Update settings and mappings of an Elasticsearch index';

    public function fire()
    {
        $configurator = $this->getConfigurator();

        $name = $configurator->getName();
        $settings = $configurator->getSettings();
        $mappings = $configurator->getMappings();

        if (!$settings && !$mappings) {
            $this->error('Nothing to update!');
            return;
        }

        $indices = ElasticClient::indices();
        $defaultParams = ['index' => $name];

        try {
            $indices->close($defaultParams);

            if ($settings) {
                $indices->putSettings(array_merge(
                    $defaultParams,
                    ['body' => ['settings' => $settings]]
                ));
            }

            if ($mappings) {
                $indices->putMapping(array_merge(
                    $defaultParams,
                    ['body' => $mappings]
                ));
            }

            $indices->open($defaultParams);
        } catch (Exception $exception) {
            $indices->open($defaultParams);

            throw $exception;
        }

        $this->info(sprintf(
            'Index %s was updated!',
            $name
        ));
    }
}