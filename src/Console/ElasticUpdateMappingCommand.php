<?php

namespace ScoutElastic\Console;

use Illuminate\Console\Command;
use LogicException;
use ScoutElastic\Console\Features\RequiresModelArgument;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Migratable;
use ScoutElastic\Payloads\TypePayload;

class ElasticUpdateMappingCommand extends Command
{
    use RequiresModelArgument;

    /**
     * {@inheritdoc}
     */
    protected $name = 'elastic:update-mapping';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Update a model mapping';

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $model = $this->getModel()) {
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

        $payload = (new TypePayload($model))
            ->set('body.'.$model->searchableAs(), $mapping)
            ->set('include_type_name', 'true');

        if (in_array(Migratable::class, class_uses_recursive($configurator))) {
            $payload->useAlias('write');
        }

        ElasticClient::indices()
            ->putMapping($payload->get());

        $this->info(sprintf(
            'The %s mapping was updated!',
            $model->searchableAs()
        ));
    }
}
