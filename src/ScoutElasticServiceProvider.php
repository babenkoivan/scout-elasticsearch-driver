<?php

namespace ScoutElastic;

use ScoutElastic\Facades\ElasticClient;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Config;
use App;

class ScoutElasticServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/scout_elastic.php' => config_path('scout_elastic.php'),
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