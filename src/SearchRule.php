<?php

namespace ScoutElastic;

use ScoutElastic\Builders\SearchBuilder;

class SearchRule
{
    /**
     * @var SearchBuilder
     */
    protected $builder;

    /**
     * @param SearchBuilder $builder
     */
    public function __construct(SearchBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return bool
     */
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