<?php

namespace ScoutElastic\Tests;

use PHPUnit_Framework_TestCase;
use Mockery;
use Config;
use Illuminate\Database\Eloquent\Collection;
use ScoutElastic\Builders\FilterBuilder;
use ScoutElastic\Builders\SearchBuilder;
use ScoutElastic\ElasticEngine;
use ScoutElastic\IndexConfigurator;
use ScoutElastic\SearchableModel;
use ScoutElastic\Facades\ElasticClient;

class ElasticEngineTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function initClient()
    {
        return Mockery::mock('alias:' . ElasticClient::class);
    }

    protected function initEngine()
    {
        Mockery::mock('alias:' . Config::class)
            ->shouldReceive('get')
            ->with('scout_elastic.update_mapping')
            ->andReturn(false);

        return new ElasticEngine();
    }

    protected function initModel($fields = [])
    {
        $indexConfigurator = Mockery::mock(IndexConfigurator::class)
            ->shouldReceive('getName')
            ->andReturn('test_index')
            ->getMock();

        return Mockery::mock(SearchableModel::class)
            ->makePartial()
            ->forceFill($fields)
            ->shouldReceive('getIndexConfigurator')
            ->andReturn($indexConfigurator)
            ->getMock()
            ->shouldReceive('searchableAs')
            ->andReturn('test_type')
            ->getMock();
    }

    public function test_if_the_update_method_of_non_existing_model_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('exists')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'id' => 1
            ])
            ->andReturn(false)
            ->getMock()
            ->shouldReceive('index')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'id' => 1,
                'body' => [
                    'id' => 1,
                    'name' => 'test model'
                ]
            ]);

        $model = $this->initModel([
            'id' => 1,
            'name' => 'test model'
        ]);

        $this->initEngine()->update(Collection::make([$model]));
    }

    public function test_if_the_update_method_of_existing_model_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('exists')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'id' => 1
            ])
            ->andReturn(true)
            ->getMock()
            ->shouldReceive('update')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'id' => 1,
                'body' => [
                    'doc' => [
                        'id' => 1,
                        'name' => 'test model'
                    ]
                ]
            ]);

        $model = $this->initModel([
            'id' => 1,
            'name' => 'test model'
        ]);

        $this->initEngine()->update(Collection::make([$model]));
    }

    public function test_if_the_delete_method_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('delete')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'id' => 1
            ]);

        $model = $this->initModel(['id' => 1]);

        $this->initEngine()->delete(Collection::make([$model]));
    }

    public function test_if_the_search_method_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'test query'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = new SearchBuilder($model, 'test query');

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_specified_limit_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'test query'
                                ]
                            ]
                        ]
                    ],
                    'size' => 10
                ]
            ]);

        $model = $this->initModel();

        $builder = (new SearchBuilder($model, 'test query'))->take(10);

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_specified_order_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'test query'
                                ]
                            ]
                        ]
                    ],
                    'sort' => [
                        ['name' => 'asc']
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = (new SearchBuilder($model, 'test query'))->orderBy('name', 'asc');

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_specified_where_clause_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'phone'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must' => [
                                        [
                                            'term' => [
                                                'brand' => 'apple'
                                            ]
                                        ],
                                        [
                                            'term' => [
                                                'color' => 'red'
                                            ]
                                        ],
                                        [
                                            'range' => [
                                                'memory' => [
                                                    'gte' => 32
                                                ]
                                            ]
                                        ],
                                        [
                                            'range' => [
                                                'battery' => [
                                                    'gt' => 1500
                                                ]
                                            ]
                                        ],
                                        [
                                            'range' => [
                                                'weight' => [
                                                    'lt' => 200
                                                ]
                                            ]
                                        ],
                                        [
                                            'range' => [
                                                'price' => [
                                                    'lte' => 700
                                                ]
                                            ]
                                        ]
                                    ],
                                    'must_not' => [
                                        [
                                            'term' => [
                                                'used' => 'yes'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = (new SearchBuilder($model, 'phone'))
            ->where('brand', 'apple')
            ->where('color', '=', 'red')
            ->where('memory', '>=', 32)
            ->where('battery', '>', 1500)
            ->where('weight', '<', 200)
            ->where('price', '<=', 700)
            ->where('used', '<>', 'yes');

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_specified_whereIn_clause_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'test query'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must' => [
                                        [
                                            'terms' => [
                                                'id' => [1, 2, 3, 4, 5]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereIn('id', [1, 2, 3, 4, 5]);

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_specified_whereNotIn_clause_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'test query'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must_not' => [
                                        [
                                            'terms' => [
                                                'id' => [1, 2, 3, 4, 5]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereNotIn('id', [1, 2, 3, 4, 5]);

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_specified_whereBetween_clause_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'test query'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must' => [
                                        [
                                            'range' => [
                                                'price' => [
                                                    'gte' => 100,
                                                    'lte' => 300
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereBetween('price', [100, 300]);

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_specified_whereNotBetween_clause_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'test query'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must_not' => [
                                        [
                                            'range' => [
                                                'price' => [
                                                    'gte' => 100,
                                                    'lte' => 300
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereNotBetween('price', [100, 300]);

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_specified_whereExists_clause_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'test query'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must' => [
                                        [
                                            'exists' => [
                                                'field' => 'sale'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereExists('sale');

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_specified_whereNotExists_clause_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'test query'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must_not' => [
                                        [
                                            'exists' => [
                                                'field' => 'sale'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereNotExists('sale');

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_specified_whereRegexp_clause_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'phone'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must' => [
                                        [
                                            'regexp' => [
                                                'brand' => [
                                                    'value' => 'a[a-z]+',
                                                    'flags' => 'ALL'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = (new SearchBuilder($model, 'phone'))->whereRegexp('brand', 'a[a-z]+', 'ALL');

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_specified_rule_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    'name' => 'John'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = (new SearchBuilder($model, 'John'))->rule(function($builder) {
            return [
                'must' => [
                    'match' => [
                        'name' => $builder->query
                    ]
                ]
            ];
        });

        $this->initEngine()->search($builder);
    }

    public function test_if_the_search_method_with_an_asterisk_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match_all' => [

                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $builder = new FilterBuilder($model);

        $this->initEngine()->search($builder);
    }

    public function test_if_the_searchRaw_method_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    'phone' => 'iphone'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->initModel();

        $this->initEngine()->searchRaw($model, [
            'query' => [
                'bool' => [
                    'must' => [
                        'match' => [
                            'phone' => 'iphone'
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function test_if_the_paginate_method_builds_correct_payload()
    {
        $this->initClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'test query'
                                ]
                            ]
                        ]
                    ],
                    'size' => 8,
                    'from' => 16
                ]
            ]);

        $model = $this->initModel();

        $builder = new SearchBuilder($model, 'test query');

        $this->initEngine()->paginate($builder, 8, 3);
    }

    protected function getElasticSearchResponse()
    {
        return [
            'took' => 2,
            'timed_out' => false,
            '_shards' => [
                'total' => 5,
                'successful' => 5,
                'failed' => 0,
            ],
            'hits' => [
                'total' => 2,
                'max_score' => 2.3862944,
                'hits' => [
                    [
                        '_index' => 'test_index',
                        '_type' => 'test_type',
                        '_id' => '1',
                        '_score' => 2.3862944,
                        '_source' => [
                            'id' => 1,
                            'name' => 'Eduardo',
                        ],
                    ],
                    [
                        '_index' => 'test_index',
                        '_type' => 'test_type',
                        '_id' => '3',
                        '_score' => 2.3862944,
                        '_source' => [
                            'id' => 3,
                            'name' => 'Roberto'
                        ],
                    ]
                ]
            ]
        ];
    }

    public function test_if_the_mapIds_method_returns_correct_ids()
    {
        $results = $this->getElasticSearchResponse();

        $this->assertEquals(
            $this->initEngine()->mapIds($results),
            ['1', '3']
        );
    }

    public function test_if_the_getTotalCount_method_returns_correct_number_of_results()
    {
        $results = $this->getElasticSearchResponse();

        $this->assertEquals($this->initEngine()->getTotalCount($results), 2);
    }

    public function test_if_the_map_method_returns_the_same_results_from_database_as_in_search_result()
    {
        $searchResults = $this->getElasticSearchResponse();

        $model = $this->initModel()

            ->shouldReceive('whereIn')
            ->with('id', [1, 3])
            ->andReturnSelf()
            ->getMock()

            ->shouldReceive('get')
            ->andReturn(Collection::make([
                $this->initModel([
                    'id' => 1,
                    'name' => 'Eduardo'
                ]),
                $this->initModel([
                    'id' => 3,
                    'name' => 'Roberto'
                ])
            ]))
            ->getMock();

        $databaseResult = $this->initEngine()->map($searchResults, $model);

        $this->assertEquals(
            array_pluck($searchResults['hits']['hits'], '_id'),
            $databaseResult->pluck('id')->all()
        );
    }
}