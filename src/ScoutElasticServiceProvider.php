<?php

namespace ScoutElastic;

use Config;
use InvalidArgumentException;
use Elasticsearch\ClientBuilder;
use Laravel\Scout\EngineManager;
use Illuminate\Support\ServiceProvider;

use ScoutElastic\Console\ElasticIndexCreateCommand;
use ScoutElastic\Console\ElasticIndexDropCommand;
use ScoutElastic\Console\ElasticIndexUpdateCommand;
use ScoutElastic\Console\ElasticMigrateCommand;
use ScoutElastic\Console\ElasticUpdateMappingCommand;

use ScoutElastic\Console\IndexConfiguratorMakeCommand;
use ScoutElastic\Console\SearchableModelMakeCommand;
use ScoutElastic\Console\SearchRuleMakeCommand;
use ScoutElastic\Console\AggregateRuleMakeCommand;
use ScoutElastic\Console\SuggestRuleMakeCommand;
use ScoutElastic\Console\HighlightRuleMakeCommand;

class ScoutElasticServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/scout.php' => config_path('scout.php'),
        ]);

        $this->commands([
            // make commands
            IndexConfiguratorMakeCommand::class,
            SearchableModelMakeCommand::class,
            SearchRuleMakeCommand::class,
            AggregateRuleMakeCommand::class,
            SuggestRuleMakeCommand::class,
            HighlightRuleMakeCommand::class,

            // elastic commands
            ElasticIndexCreateCommand::class,
            ElasticIndexUpdateCommand::class,
            ElasticIndexDropCommand::class,
            ElasticUpdateMappingCommand::class,
            ElasticMigrateCommand::class
        ]);

        require_once __DIR__.'/Macros.php';

        $this->app->make(EngineManager::class)
            ->extend('elastic', function () {
                $indexerType = config('scout.es.indexer', 'single');
                $updateMapping = config('scout.es.update_mapping', true);
                $trackScores = config('scout.es.track_scores', true);

                $indexerClass = '\\ScoutElastic\\Indexers\\'.ucfirst($indexerType).'Indexer';

                if (!class_exists($indexerClass)) {
                    throw new InvalidArgumentException(sprintf(
                        'The %s indexer doesn\'t exist.',
                        $indexerType
                    ));
                }

                return new ElasticEngine(new $indexerClass(), $updateMapping, $trackScores);
            });
    }

    public function register()
    {
        $this->app->singleton('scout.es.client', function() {
            $config = Config::get('scout.es.client');
            return ClientBuilder::fromConfig($config);
        });
    }
}