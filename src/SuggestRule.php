<?php

namespace ScoutElastic;

use ScoutElastic\Builders\SearchBuilder;

class SuggestRule
{
    protected $builder;

    public function __construct(SearchBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function buildSuggestPayload()
    {
        //
    }
}