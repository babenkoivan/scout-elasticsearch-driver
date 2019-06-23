<?php

namespace ScoutElastic;

use ScoutElastic\Builders\SearchBuilder;

class SearchRule
{
    /**
     * The builder.
     *
     * @var \ScoutElastic\Builders\SearchBuilder
     */
    protected $builder;

    /**
     * SearchRule constructor.
     *
     * @param \ScoutElastic\Builders\SearchBuilder $builder
     * @return void
     */
    public function __construct(SearchBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Check if this is applicable.
     *
     * @return bool
     */
    public function isApplicable()
    {
        return true;
    }

    /**
     * Build the highlight payload.
     *
     * @return array|null
     */
    public function buildHighlightPayload()
    {
    }

    /**
     * Build the query payload.
     *
     * @return array
     */
    public function buildQueryPayload()
    {
        return [
            'must' => [
                'query_string' => [
                    'query' => $this->builder->query,
                ],
            ],
        ];
    }
}
