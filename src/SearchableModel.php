<?php

namespace ScoutElastic;

use Illuminate\Database\Eloquent\Model;

/** @deprecated Use the \ScoutElastic\Searchable trait instead. */
abstract class SearchableModel extends Model
{
    protected $indexConfigurator;

    protected $mapping = [];

    protected $searchRules = [
        SearchRule::class
    ];

    use Searchable;
}