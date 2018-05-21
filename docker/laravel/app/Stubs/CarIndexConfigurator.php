<?php

namespace App\Stubs;

use ScoutElastic\IndexConfigurator;
use ScoutElastic\Migratable;

class CarIndexConfigurator extends IndexConfigurator
{
    use Migratable;

    protected $settings = [
        //
    ];
}
