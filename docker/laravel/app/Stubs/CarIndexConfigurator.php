<?php

namespace App\Stubs;

use ScoutElastic\Migratable;
use ScoutElastic\IndexConfigurator;

class CarIndexConfigurator extends IndexConfigurator
{
    use Migratable;

    protected $settings = [
        //
    ];
}
