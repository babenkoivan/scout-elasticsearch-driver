<?php

namespace ScoutElastic\Tests\Stubs;

use ScoutElastic\Searchable;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use Searchable;
}