<?php

namespace ScoutElastic\Tests;

use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Searchable;

class ModelStub extends Model
{
    use Searchable;
}