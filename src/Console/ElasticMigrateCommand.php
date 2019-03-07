<?php

namespace ScoutElastic\Console;

use Exception;
use ScoutElastic\Migratable;
use Illuminate\Console\Command;
use ScoutElastic\Payloads\RawPayload;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Payloads\IndexPayload;
use Symfony\Component\Console\Input\InputArgument;
use ScoutElastic\Console\Features\RequiresModelArgument;

class ElasticMigrateCommand extends Command
{
    use RequiresModelArgument {
        RequiresModelArgument::getArguments as private modelArgument;
    }

    /**
     * {@inheritdoc}
     */
    protected $name = 'elastic:migrate';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Migrate model to another index';

    /**
     * Get the command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        $arguments = $this->modelArgument();

        $arguments[] = ['target-index', InputArgument::REQUIRED, 'The index name to migrate'];

        return $arguments;
    }

    /**
     * Checks if the target index exists.
     *
     * @return bool
     */
    protected function isTargetIndexExists()
    {
        $targetIndex = $this->argument('target-index');

        $payload = (new RawPayload())
            ->set('index', $targetIndex)
            ->get();

        return ElasticClient::indices()
            ->exists($payload);
    }

    /**
     * Create a target index.
     *
     * @return void
     */
    protected function createTargetIndex()
    {
        $targetIndex = $this->argument('target-index');

        $sourceIndexConfigurator = $this->getModel()
            ->getIndexConfigurator();

        $payload = (new RawPayload())
            ->set('index', $targetIndex)
            ->setIfNotEmpty('body.settings', $sourceIndexConfigurator->getSettings())
            ->setIfNotEmpty('body.mappings._default_', $sourceIndexConfigurator->getDefaultMapping())
            ->get();

        ElasticClient::indices()
            ->create($payload);

        $this->info(sprintf(
            'The %s index was created.',
            $targetIndex
        ));
    }

    /**
     * Update the target index.
     *
     * @throws \Exception
     * @return void
     */
    protected function updateTargetIndex()
    {
        $targetIndex = $this->argument('target-index');

        $sourceIndexConfigurator = $this->getModel()
            ->getIndexConfigurator();

        $targetIndexPayload = (new RawPayload())
            ->set('index', $targetIndex)
            ->get();

        $indices = ElasticClient::indices();

        try {
            $indices->close($targetIndexPayload);

            if ($settings = $sourceIndexConfigurator->getSettings()) {
                $targetIndexSettingsPayload = (new RawPayload())
                    ->set('index', $targetIndex)
                    ->set('body.settings', $settings)
                    ->get();

                $indices->putSettings($targetIndexSettingsPayload);
            }

            if ($defaultMapping = $sourceIndexConfigurator->getDefaultMapping()) {
                $targetIndexMappingPayload = (new RawPayload())
                    ->set('index', $targetIndex)
                    ->set('type', '_default_')
                    ->set('body._default_', $defaultMapping)
                    ->get();

                $indices->putMapping($targetIndexMappingPayload);
            }

            $indices->open($targetIndexPayload);
        } catch (Exception $exception) {
            $indices->open($targetIndexPayload);

            throw $exception;
        }

        $this->info(sprintf(
            'The index %s was updated.',
            $targetIndex
        ));
    }

    /**
     * Update the target index mapping.
     *
     * @return void
     */
    protected function updateTargetIndexMapping()
    {
        $sourceModel = $this->getModel();
        $sourceIndexConfigurator = $sourceModel->getIndexConfigurator();

        $targetIndex = $this->argument('target-index');
        $targetType = $sourceModel->searchableAs();

        $mapping = array_merge_recursive(
            $sourceIndexConfigurator->getDefaultMapping(),
            $sourceModel->getMapping()
        );

        if (empty($mapping)) {
            $this->warn(sprintf(
                'The %s mapping is empty.',
                get_class($sourceModel)
            ));

            return;
        }

        $payload = (new RawPayload())
            ->set('index', $targetIndex)
            ->set('type', $targetType)
            ->set('body.'.$targetType, $mapping)
            ->get();

        ElasticClient::indices()
            ->putMapping($payload);

        $this->info(sprintf(
            'The %s mapping was updated.',
            $targetIndex
        ));
    }

    /**
     * Check if an alias exists.
     *
     * @param string $name
     * @return bool
     */
    protected function isAliasExists($name)
    {
        $payload = (new RawPayload())
            ->set('name', $name)
            ->get();

        return ElasticClient::indices()
            ->existsAlias($payload);
    }

    /**
     * Get an alias.
     *
     * @param string $name
     * @return array
     */
    protected function getAlias($name)
    {
        $getPayload = (new RawPayload())
            ->set('name', $name)
            ->get();

        return ElasticClient::indices()
            ->getAlias($getPayload);
    }

    /**
     * Delete an alias.
     *
     * @param string $name
     * @return void
     */
    protected function deleteAlias($name)
    {
        $aliases = $this->getAlias($name);

        if (empty($aliases)) {
            return;
        }

        foreach ($aliases as $index => $alias) {
            $deletePayload = (new RawPayload())
                ->set('index', $index)
                ->set('name', $name)
                ->get();

            ElasticClient::indices()
                ->deleteAlias($deletePayload);

            $this->info(sprintf(
                'The %s alias for the %s index was deleted.',
                $name,
                $index
            ));
        }
    }

    /**
     * Create an alias for the target index.
     *
     * @param string $name
     * @return void
     */
    protected function createAliasForTargetIndex($name)
    {
        $targetIndex = $this->argument('target-index');

        if ($this->isAliasExists($name)) {
            $this->deleteAlias($name);
        }

        $payload = (new RawPayload())
            ->set('index', $targetIndex)
            ->set('name', $name)
            ->get();

        ElasticClient::indices()
            ->putAlias($payload);

        $this->info(sprintf(
            'The %s alias for the %s index was created.',
            $name,
            $targetIndex
        ));
    }

    /**
     * Import the documents to the target index.
     *
     * @return void
     */
    protected function importDocumentsToTargetIndex()
    {
        $sourceModel = $this->getModel();

        $this->call(
            'scout:import',
            ['model' => get_class($sourceModel)]
        );
    }

    /**
     * Delete the source index.
     *
     * @return void
     */
    protected function deleteSourceIndex()
    {
        $sourceIndexConfigurator = $this
            ->getModel()
            ->getIndexConfigurator();

        if ($this->isAliasExists($sourceIndexConfigurator->getName())) {
            $aliases = $this->getAlias($sourceIndexConfigurator->getName());

            foreach ($aliases as $index => $alias) {
                $payload = (new RawPayload())
                    ->set('index', $index)
                    ->get();

                ElasticClient::indices()
                    ->delete($payload);

                $this->info(sprintf(
                    'The %s index was removed.',
                    $index
                ));
            }
        } else {
            $payload = (new IndexPayload($sourceIndexConfigurator))
                ->get();

            ElasticClient::indices()
                ->delete($payload);

            $this->info(sprintf(
                'The %s index was removed.',
                $sourceIndexConfigurator->getName()
            ));
        }
    }

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        $sourceModel = $this->getModel();
        $sourceIndexConfigurator = $sourceModel->getIndexConfigurator();

        if (! in_array(Migratable::class, class_uses_recursive($sourceIndexConfigurator))) {
            $this->error(sprintf(
                'The %s index configurator must use the %s trait.',
                get_class($sourceIndexConfigurator),
                Migratable::class
            ));

            return;
        }

        $this->isTargetIndexExists() ? $this->updateTargetIndex() : $this->createTargetIndex();

        $this->updateTargetIndexMapping();

        $this->createAliasForTargetIndex($sourceIndexConfigurator->getWriteAlias());

        $this->importDocumentsToTargetIndex();

        $this->deleteSourceIndex();

        $this->createAliasForTargetIndex($sourceIndexConfigurator->getName());

        $this->info(sprintf(
            'The %s model successfully migrated to the %s index.',
            get_class($sourceModel),
            $this->argument('target-index')
        ));
    }
}
