<?php

namespace ScoutElastic\Tests;

use stdClass;
use ScoutElastic\ElasticEngine;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Builders\FilterBuilder;
use ScoutElastic\Builders\SearchBuilder;
use ScoutElastic\Tests\Stubs\SearchRule;
use ScoutElastic\Tests\Dependencies\Model;

class ElasticEngineTest extends AbstractTestCase
{
    use Model;

    /**
     * @var ElasticEngine
     */
    private $engine;

    protected function setUp()
    {
        $this->engine = $this
            ->getMockBuilder(ElasticEngine::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    public function testBuildSearchQueryPayloadCollection()
    {
        $model = $this->mockModel();

        $searchBuilder = (new SearchBuilder($model, 'foo'))
            ->rule(SearchRule::class)
            ->rule(function (SearchBuilder $searchBuilder) {
                return [
                    'must' => [
                        'match' => [
                            'bar' => $searchBuilder->query,
                        ],
                    ],
                ];
            })
            ->select('title')
            ->select(['price', 'color'])
            ->where('id', '>', 20)
            ->orderBy('id', 'asc')
            ->collapse('brand')
            ->take(10)
            ->from(100);

        $payloadCollection = $this
            ->engine
            ->buildSearchQueryPayloadCollection($searchBuilder);

        $this->assertEquals(
            [
                [
                    'index' => 'test',
                    'type' => 'test',
                    'body' => [
                        '_source' => [
                            'title',
                            'price',
                            'color',
                        ],
                        'query' => [
                            'bool' => [
                                'must' => [
                                    'query_string' => [
                                        'query' => 'foo',
                                    ],
                                ],
                                'filter' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'range' => [
                                                    'id' => [
                                                        'gt' => 20,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'highlight' => [
                            'fields' => [
                                'title' => [
                                    'type' => 'plain',
                                ],
                                'price' => [
                                    'type' => 'plain',
                                ],
                                'color' => [
                                    'type' => 'plain',
                                ],
                            ],
                        ],
                        'collapse' => [
                            'field' => 'brand',
                        ],
                        'sort' => [
                            [
                                'id' => 'asc',
                            ],
                        ],
                        'from' => 100,
                        'size' => 10,
                    ],
                ],
                [
                    'index' => 'test',
                    'type' => 'test',
                    'body' => [
                        '_source' => [
                            'title',
                            'price',
                            'color',
                        ],
                        'query' => [
                            'bool' => [
                                'must' => [
                                    'match' => [
                                        'bar' => 'foo',
                                    ],
                                ],
                                'filter' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'range' => [
                                                    'id' => [
                                                        'gt' => 20,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'collapse' => [
                            'field' => 'brand',
                        ],
                        'sort' => [
                            [
                                'id' => 'asc',
                            ],
                        ],
                        'from' => 100,
                        'size' => 10,
                    ],
                ],
            ],
            $payloadCollection->all()
        );
    }

    public function testBuildFilterQueryPayloadCollection()
    {
        $model = $this->mockModel();

        $filterBuilder = (new FilterBuilder($model))
            ->where('foo', 'bar')
            ->orderBy('foo', 'desc')
            ->take(1)
            ->from(30);

        $payloadCollection = $this
            ->engine
            ->buildSearchQueryPayloadCollection($filterBuilder);

        $this->assertEquals(
            [
                [
                    'index' => 'test',
                    'type' => 'test',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    'match_all' => new stdClass(),
                                ],
                                'filter' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'term' => [
                                                    'foo' => 'bar',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'sort' => [
                            [
                                'foo' => 'desc',
                            ],
                        ],
                        'from' => 30,
                        'size' => 1,
                    ],
                ],
            ],
            $payloadCollection->all()
        );
    }

    public function testCount()
    {
        ElasticClient
            ::shouldReceive('count')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'body' => [
                    '_source' => [
                        'title',
                    ],
                    'query' => [
                        'bool' => [
                            'must' => [
                                'query_string' => [
                                    'query' => 'foo',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $model = $this->mockModel();

        $searchBuilder = (new SearchBuilder($model, 'foo'))
            ->rule(SearchRule::class)
            ->select('title');

        $this
            ->engine
            ->count($searchBuilder);

        $this->addToAssertionCount(1);
    }

    public function testSearchRaw()
    {
        ElasticClient
            ::shouldReceive('search')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'body' => [
                    'query' => [
                        'match' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
            ]);

        $model = $this->mockModel();

        $query = [
            'query' => [
                'match' => [
                    'foo' => 'bar',
                ],
            ],
        ];

        $this
            ->engine
            ->searchRaw(
                $model,
                $query
            );

        $this->addToAssertionCount(1);
    }

    public function testMapIds()
    {
        $results = [
            'hits' => [
                'hits' => [
                    ['_id' => 1],
                    ['_id' => 2],
                ],
            ],
        ];

        $this->assertEquals(
            [1, 2],
            $this->engine->mapIds($results)->all()
        );
    }

    public function testMapWithoutTrashed()
    {
        $this->markTestSkipped();

        $results = [
            'hits' => [
                'total' => 2,
                'hits' => [
                    [
                        '_id' => 1,
                        '_source' => [
                            'title' => 'foo',
                        ],
                    ],
                    [
                        '_id' => 2,
                        '_source' => [
                            'title' => 'bar',
                        ],
                    ],
                ],
            ],
        ];

        $model = $this->mockModel([
            'key' => 2,
            'methods' => [
                'usesSoftDelete',
                'newQuery',
                'whereIn',
                'get',
                'keyBy',
            ],
        ]);

        $model
            ->method('usesSoftDelete')
            ->willReturn(false);

        $model
            ->method('newQuery')
            ->willReturn($model);

        $model
            ->method('whereIn')
            ->willReturn($model);

        $model
            ->method('get')
            ->willReturn($model);

        $model
            ->method('keyBy')
            ->willReturn([
                2 => $model,
            ]);

        $builder = $this
            ->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(
            [$model],
            $this->engine->map($builder, $results, $model)->all()
        );
    }

    public function testMapWithTrashed()
    {
        $this->markTestSkipped();

        $results = [
            'hits' => [
                'total' => 2,
                'hits' => [
                    [
                        '_id' => 1,
                        '_source' => [
                            'title' => 'foo',
                        ],
                    ],
                    [
                        '_id' => 2,
                        '_source' => [
                            'title' => 'bar',
                        ],
                    ],
                ],
            ],
        ];

        $model = $this->mockModel([
            'key' => 2,
            'methods' => [
                'usesSoftDelete',
                'withTrashed',
                'whereIn',
                'get',
                'keyBy',
            ],
        ]);

        $model
            ->method('usesSoftDelete')
            ->willReturn(true);

        $model
            ->method('withTrashed')
            ->willReturn($model);

        $model
            ->method('whereIn')
            ->willReturn($model);

        $model
            ->method('get')
            ->willReturn($model);

        $model
            ->method('keyBy')
            ->willReturn([
                2 => $model,
            ]);

        $builder = $this
            ->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(
            [$model],
            $this->engine->map($builder, $results, $model)->all()
        );
    }

    public function testGetTotalCount()
    {
        $results = [
            'hits' => [
                'total' => 100,
            ],
        ];

        $this->assertEquals(
            100,
            $this->engine->getTotalCount($results)
        );
    }
}
