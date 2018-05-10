<?php

namespace ScoutElastic\Console;

use LogicException;
use Illuminate\Console\Command;
use ScoutElastic\Console\Features\RequiresModelArgument;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Migratable;
use ScoutElastic\Payloads\TypePayload;

class ElasticUpdateMappingCommand extends Command
{
    use RequiresModelArgument;

    /**
     * @var string
     */
    protected $name = 'elastic:update-mapping';

    /**
     * @var string
     */
    protected $description = 'Update a model mapping';

    public function handle()
    {
        if (!$model = $this->getModel()) {
            return;
        }

        $configurator = $model->getIndexConfigurator();

        $mapping = array_merge_recursive(
            $configurator->getDefaultMapping(),
            $model->getMapping()
        );

        if (empty($mapping)) {
            throw new LogicException('Nothing to update: the mapping is not specified.');
        }

        $payload = new TypePayload($model);

        if (in_array(Migratable::class, class_uses_recursive($configurator))) {
            $payload->useAlias('write');
        }

        $payload->set('body.' . $model->searchableAs(), $mapping);

        ElasticClient::indices()
            ->putMapping($payload->get());

        $this->info(sprintf(
            'The %s mapping was updated!',
            $model->searchableAs()
        ));
    }
}