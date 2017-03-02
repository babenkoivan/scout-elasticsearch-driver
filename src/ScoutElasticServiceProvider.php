<?php

namespace ScoutElastic;

use App;
use Config;
use Illuminate\Support\ServiceProvider;
use Elasticsearch\ClientBuilder;
use ScoutElastic\Console\ElasticIndexCreateCommand;
use ScoutElastic\Console\ElasticIndexDropCommand;
use ScoutElastic\Console\IndexConfiguratorMakeCommand;

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

            // elastic commands
            ElasticIndexCreateCommand::class,
            ElasticIndexDropCommand::class,
        ]);
    }

    public function register()
    {
        App::singleton('scout_elastic.client', function() {
            $config = Config::get('scout_elastic.client');
            return ClientBuilder::fromConfig($config);
        });
    }
}