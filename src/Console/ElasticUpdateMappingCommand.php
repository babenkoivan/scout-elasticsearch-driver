<?php

namespace ScoutElastic\Console;

use LogicException;
use Illuminate\Console\Command;
use ScoutElastic\Console\Features\requiresModelArgument;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Payloads\TypePayload;

class ElasticUpdateMappingCommand extends Command
{
    use requiresModelArgument;

    protected $name = 'elastic:update-mapping';

    protected $description = 'Update a model mapping';

    public function handle() {
        $model = $this->getModel();

        $configurator = $model->getIndexConfigurator();

        $mapping = array_merge_recursive($configurator->getDefaultMapping(), $model->getMapping());

        if (empty($mapping)) {
            throw new LogicException('Nothing to update: the mapping is not specified.');
        }

        $payload = (new TypePayload($model))
            ->set('body.'.$model->searchableAs(), $mapping)
            ->get();

        ElasticClient::indices()
            ->putMapping($payload);

        $this->info(sprintf(
            'The %s mapping was updated!',
            $model->searchableAs()
        ));
    }
}