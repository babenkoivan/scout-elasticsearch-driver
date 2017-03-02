<?php

namespace ScoutElastic\Facades;

use Illuminate\Support\Facades\Facade;

class ElasticClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'scout_elastic.client';
    }
}