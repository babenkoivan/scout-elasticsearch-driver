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

    public function isApplicable()
    {
        return true;
    }

    /**
     * @return array|null
     */
    public function buildHighlightPayload()
    {
        return null;
    }

    /**
     * @return array
     */
    public function buildQueryPayload()
    {
        return [
            'must' => [
                'query_string' => [
                    'query' => $this->builder->query
                ]
            ]
        ];
    }
}