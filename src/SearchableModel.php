<?php

namespace ScoutElastic;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

abstract class SearchableModel extends Model
{
    use Searchable;

    protected $indexConfigurator;

    protected $mapping = [];
}