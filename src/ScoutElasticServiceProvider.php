<?php

namespace ScoutElastic;

use Config;
use Illuminate\Support\ServiceProvider;
use Elasticsearch\ClientBuilder;
use ScoutElastic\Console\ElasticIndexCreateCommand;
use ScoutElastic\Console\ElasticIndexDropCommand;
use ScoutElastic\Console\ElasticIndexUpdateCommand;
use ScoutElastic\Console\ElasticMigrateCommand;
use ScoutElastic\Console\ElasticUpdateMappingCommand;
use ScoutElastic\Console\IndexConfiguratorMakeCommand;
use ScoutElastic\Console\SearchableModelMakeCommand;
use Laravel\Scout\EngineManager;
use ScoutElastic\Console\SearchRuleMakeCommand;

class ScoutElasticServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/scout_elastic.php' => config_path('scout_elastic.php'),
        ]);

        $this->commands([
            // make commands
            IndexConfiguratorMakeCommand::class,
            SearchableModelMakeCommand::class,
            SearchRuleMakeCommand::class,

            // elastic commands
            ElasticIndexCreateCommand::class,
            ElasticIndexUpdateCommand::class,
            ElasticIndexDropCommand::class,
            ElasticUpdateMappingCommand::class,
            ElasticMigrateCommand::class
        ]);

        $this->app->make(EngineManager::class)
            ->extend('elastic', function () {
                return new ElasticEngine();
            });
    }

    public function register()
    {
        $this->app->singleton('scout_elastic.client', function() {
            $config = Config::get('scout_elastic.client');
            return ClientBuilder::fromConfig($config);
        });
    }
}