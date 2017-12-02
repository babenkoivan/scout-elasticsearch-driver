<?php

namespace ScoutElastic\Tests;

use Illuminate\Database\Eloquent\Collection;
use Mockery;
use ScoutElastic\Builders\FilterBuilder;
use ScoutElastic\Builders\SearchBuilder;
use ScoutElastic\ElasticEngine;
use ScoutElastic\Indexers\BulkIndexer;
use ScoutElastic\Indexers\SingleIndexer;
use ScoutElastic\Tests\Stubs\ModelStub;
use stdClass;

class ElasticEngineTest extends TestCase
{
    protected function mockModel($fields = [])
    {
        return Mockery::mock(ModelStub::class)
            ->makePartial()
            ->forceFill($fields);
    }

    protected function buildEngine($indexer = null, $updateMapping = false)
    {
        if (is_null($indexer)) {
            $indexer = new SingleIndexer();
        }

        return new ElasticEngine($indexer, $updateMapping);
    }

    public function test_if_the_update_method_with_single_indexer_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('index')
            ->with([
                'index' => 'test_index_write',
                'type' => 'test_table',
                'id' => 1,
                'body' => [
                    'id' => 1,
                    'test_field' => 'test text'
                ]
            ]);

        $model = $this->mockModel([
            'id' => 1,
            'test_field' => 'test text'
        ]);

        $this->buildEngine()
            ->update(Collection::make([$model]));

        $this->addToAssertionCount(1);
    }

    public function test_if_the_delete_method_with_single_indexer_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('delete')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
                'id' => 1
            ]);

        $model = $this->mockModel(['id' => 1]);

        $this->buildEngine()
            ->delete(Collection::make([$model]));

        $this->addToAssertionCount(1);
    }

    public function test_if_the_update_method_with_bulk_indexer_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('bulk')
            ->with([
                'index' => 'test_index_write',
                'type' => 'test_table',
                'body' => [
                    [
                        'index' => [
                            '_id' => 1
                        ]
                    ],
                    [
                        'id' => 1,
                        'test_field' => 'test text'
                    ]
                ]
            ]);

        $model = $this->mockModel([
            'id' => 1,
            'test_field' => 'test text'
        ]);

        $this->buildEngine(new BulkIndexer())
            ->update(Collection::make([$model]));

        $this->addToAssertionCount(1);
    }

    public function test_if_the_delete_method_with_bulk_indexer_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('bulk')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
                'body' => [
                    [
                        'delete' => [
                            '_id' => 1
                        ]
                    ]
                ]
            ]);

        $model = $this->mockModel(['id' => 1]);

        $this->buildEngine(new BulkIndexer())
            ->delete(Collection::make([$model]));

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = new SearchBuilder($model, 'test query');

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_limit_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'test query'))->take(10);

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_order_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'test query'))->orderBy('name', 'asc');

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_where_clause_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'phone'))
            ->where('brand', 'apple')
            ->where('color', '=', 'red')
            ->where('memory', '>=', 32)
            ->where('battery', '>', 1500)
            ->where('weight', '<', 200)
            ->where('price', '<=', 700)
            ->where('used', '<>', 'yes');

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_whereIn_clause_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereIn('id', [1, 2, 3, 4, 5]);

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_whereNotIn_clause_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereNotIn('id', [1, 2, 3, 4, 5]);

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_whereBetween_clause_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereBetween('price', [100, 300]);

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_whereNotBetween_clause_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereNotBetween('price', [100, 300]);

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_whereExists_clause_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereExists('sale');

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_whereNotExists_clause_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'test query'))->whereNotExists('sale');

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_whereRegexp_clause_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'phone'))->whereRegexp('brand', 'a[a-z]+', 'ALL');

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_whereGeoDistance_clause_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'flat'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must' => [
                                        [
                                            'geo_distance' => [
                                                'distance' => 1000,
                                                'location' => [-70, 40]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'flat'))->whereGeoDistance(
            'location',
            [-70, 40],
            1000
        );

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_whereGeoBoundingBox_clause_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'flat'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must' => [
                                        [
                                            'geo_bounding_box' => [
                                                'location' => [
                                                    'top_left' => [-74.1, 40.73],
                                                    'bottom_right' => [-71.12, 40.01]
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'flat'))->whereGeoBoundingBox(
            'location',
            [
                'top_left' => [-74.1, 40.73],
                'bottom_right' => [-71.12, 40.01]
            ]
        );

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_whereGeoPolygon_clause_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    '_all' => 'flat'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must' => [
                                        [
                                            'geo_polygon' => [
                                                'location' => [
                                                    'points' => [
                                                        [-70, 40],
                                                        [-80, 30],
                                                        [-90, 20]
                                                    ]
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'flat'))->whereGeoPolygon(
            'location',
            [
                [-70, 40],
                [-80, 30],
                [-90, 20]
            ]
        );

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_specified_rule_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = (new SearchBuilder($model, 'John'))->rule(function ($builder) {
            return [
                'must' => [
                    'match' => [
                        'name' => $builder->query
                    ]
                ]
            ];
        });

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_search_method_with_an_asterisk_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match_all' => new stdClass()
                            ]
                        ]
                    ]
                ]
            ]);

        $model = $this->mockModel();

        $builder = new FilterBuilder($model);

        $this->buildEngine()
            ->search($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_searchRaw_method_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $this->buildEngine()
            ->searchRaw($model, [
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

        $this->addToAssertionCount(1);
    }

    public function test_if_the_paginate_method_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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

        $model = $this->mockModel();

        $builder = new SearchBuilder($model, 'test query');

        $this->buildEngine()
            ->paginate($builder, 8, 3);

        $this->addToAssertionCount(1);
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
                        '_type' => 'test_table',
                        '_id' => '1',
                        '_score' => 2.3862944,
                        '_source' => [
                            'id' => 1,
                            'test_field' => 'the first item content',
                        ],
                    ],
                    [
                        '_index' => 'test_index',
                        '_type' => 'test_table',
                        '_id' => '3',
                        '_score' => 2.3862944,
                        '_source' => [
                            'id' => 3,
                            'test_field' => 'the second item content'
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
            $this->buildEngine()->mapIds($results),
            ['1', '3']
        );
    }

    public function test_if_the_getTotalCount_method_returns_correct_number_of_results()
    {
        $results = $this->getElasticSearchResponse();

        $this->assertEquals($this->buildEngine()->getTotalCount($results), 2);
    }

    public function test_if_the_map_method_returns_the_same_results_from_database_as_in_search_result()
    {
        $searchResults = $this->getElasticSearchResponse();

        $model = $this->mockModel()
            ->shouldReceive('whereIn')
            ->with('id', [1, 3])
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('get')
            ->andReturn(Collection::make([
                $this->mockModel(['id' => 1]),
                $this->mockModel(['id' => 3])
            ]))
            ->getMock();

        $databaseResult = $this->buildEngine()->map($searchResults, $model);

        $this->assertEquals(
            array_pluck($searchResults['hits']['hits'], '_id'),
            $databaseResult->pluck('id')->all()
        );
    }

    public function test_if_the_explain_method_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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
                    'explain' => true
                ]
            ]);

        $model = $this->mockModel();

        $builder = new SearchBuilder($model, 'test query');

        $this->buildEngine()
            ->explain($builder);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_profile_method_builds_correct_payload()
    {
        $this->mockClient()
            ->shouldReceive('search')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
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
                    'profile' => true
                ]
            ]);

        $model = $this->mockModel();

        $builder = new SearchBuilder($model, 'test query');

        $this->buildEngine()
            ->profile($builder);

        $this->addToAssertionCount(1);
    }
}