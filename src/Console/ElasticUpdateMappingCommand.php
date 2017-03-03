<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\SearchableModel;
use Symfony\Component\Console\Input\InputArgument;

class ElasticUpdateMappingCommand extends Command
{
    protected $name = 'elastic:update-mapping';

    protected $description = 'Update a model mapping';

    /**
     * @return SearchableModel
     */
    protected function getModel()
    {
        $model = trim($this->argument('model'));
        return (new $model);
    }

    protected function buildPayload()
    {
        $model = $this->getModel();
        $configurator = $model->getIndexConfigurator();

        $mapping = [];

        if ($defaultMapping = $configurator->getDefaultMapping()) {
            $mapping = array_merge($mapping, $defaultMapping);
        }

        if ($modelMapping = $model->getMapping()) {
            $mapping = array_merge($mapping, $modelMapping);
        }

        if (!$mapping) {
            return null;
        }

        return [
            'index' => $configurator->getName(),
            'type' => $model->getIndexType(),
            'body' => [$model->getIndexType() => $mapping]
        ];
    }

    public function fire() {
        $model = $this->getModel();

        if ($payload = $this->buildPayload()) {
            ElasticClient::indices()
                ->putMapping($payload);
        }

        $this->info(sprintf(
            'The %s mapping was updated!',
            $model->getIndexType()
        ));
    }

    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The model class'],
        ];
    }
}