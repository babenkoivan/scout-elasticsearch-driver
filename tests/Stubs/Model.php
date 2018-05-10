<?php

namespace ScoutElastic\Tests\Stubs;

use Illuminate\Database\Eloquent\SoftDeletes;
use ScoutElastic\Searchable;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use Searchable, SoftDeletes;
}