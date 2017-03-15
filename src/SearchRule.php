<?php

namespace ScoutElastic;

use ScoutElastic\Builders\SearchBuilder;

class SearchRule
{
    protected $builder;

    public function __construct(SearchBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function buildQueryPayload()
    {
        return [
            'must' => [
                'match' => [
                    '_all' => $this->builder->query
                ]
            ]
        ];
    }
}