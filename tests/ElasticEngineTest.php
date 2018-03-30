<?php

namespace ScoutElastic\Tests;

use PHPUnit\Framework\TestCase;
use ScoutElastic\Builders\FilterBuilder;
use ScoutElastic\Builders\SearchBuilder;
use ScoutElastic\ElasticEngine;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\SearchRule;
use ScoutElastic\Tests\Dependencies\Model;
use stdClass;

class ElasticEngineTest extends TestCase
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
            ->rule(function(SearchBuilder $searchBuilder) {
                return [
                    'must' => [
                        'match' => [
                            'bar' => $searchBuilder->query
                        ]
                    ]
                ];
            })
            ->where('id', '>', 20)
            ->orderBy('id', 'asc')
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
                        'query' => [
                            'bool' => [
                                'must' => [
                                    'query_string' => [
                                        'query' => 'foo'
                                    ]
                                ],
                                'filter' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'range' => [
                                                    'id' => [
                                                        'gt' => 20
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'sort' => [
                            [
                                'id' => 'asc'
                            ]
                        ],
                        'from' => 100,
                        'size' => 10
                    ],
                ],
                [
                    'index' => 'test',
                    'type' => 'test',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    'match' => [
                                        'bar' => 'foo'
                                    ]
                                ],
                                'filter' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'range' => [
                                                    'id' => [
                                                        'gt' => 20
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'sort' => [
                            [
                                'id' => 'asc'
                            ]
                        ],
                        'from' => 100,
                        'size' => 10
                    ]
                ]
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
                                    'match_all' => new stdClass()
                                ],
                                'filter' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'term' => [
                                                    'foo' => 'bar'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'sort' => [
                            [
                                'foo' => 'desc'
                            ]
                        ],
                        'from' => 30,
                        'size' => 1
                    ],
                ]
            ],
            $payloadCollection->all()
        );
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
                            'foo' => 'bar'
                        ]
                    ]
                ]
            ]);

        $model = $this->mockModel();

        $query = [
            'query' => [
                'match' => [
                    'foo' => 'bar'
                ]
            ]
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
                    ['_id' => 2]
                ]
            ]
        ];

        $this->assertEquals(
            [1, 2],
            $this->engine->mapIds($results)
        );
    }

    public function testMap()
    {
        $results = [
            'hits' => [
                'total' => 2,
                'hits' => [
                    ['_id' => 1, '_score' => 1.0],
                    ['_id' => 2, '_score' => 1.0]
                ]
            ],
            'builder' => $this->getMockBuilder(FilterBuilder::class)
        ];

        $model = $this->mockModel([
            'key' => 2,
            'methods' => [
                'whereIn',
                'get',
                'keyBy'
            ]
        ]);

        $model
            ->method('whereIn')
            ->willReturn($model);

        $model
            ->method('get')
            ->willReturn($model);

        $model
            ->method('keyBy')
            ->willReturn([
                2 => $model
            ]);

        $this->assertEquals(
            [$model],
            $this->engine->map($results, $model)->all()
        );
    }

    public function testGetTotalCount()
    {
        $results = [
            'hits' => [
                'total' => 100
            ]
        ];

        $this->assertEquals(
            100,
            $this->engine->getTotalCount($results)
        );
    }
}