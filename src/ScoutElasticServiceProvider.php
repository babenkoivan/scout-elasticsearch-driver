<?php

namespace ScoutElastic;

use InvalidArgumentException;
use Elasticsearch\ClientBuilder;
use Laravel\Scout\EngineManager;
use Illuminate\Support\Facades\Config;
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

        $this
            ->app
            ->make(EngineManager::class)
            ->extend('elastic', function () {
                $indexerType = config('scout_elastic.indexer', 'single');
                $updateMapping = config('scout_elastic.update_mapping', true);
                $trackScores = config('scout_elastic.track_scores', true);

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
        $this
            ->app
            ->singleton('scout_elastic.client', function() {
                $config = Config::get('scout_elastic.client');
                return ClientBuilder::fromConfig($config);
            });
    }
}