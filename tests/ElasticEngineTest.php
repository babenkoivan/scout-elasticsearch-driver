<?php

namespace ScoutElastic\Tests;

use Illuminate\Database\Eloquent\Collection;
use PHPUnit_Framework_TestCase;
use Mockery;
use ScoutElastic\Builders\SearchBuilder;
use ScoutElastic\ElasticEngine;
use ScoutElastic\IndexConfigurator;
use ScoutElastic\SearchableModel;
use Config;
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

        $this->initEngine()
            ->update(Collection::make([$model]));
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

        $this->initEngine()
            ->update(Collection::make([$model]));
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

        $this->initEngine()
            ->delete(Collection::make([$model]));
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

        $this->initEngine()
            ->search($builder);
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

        $this->initEngine()
            ->search($builder);
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

        $this->initEngine()
            ->search($builder);
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
                                    '_all' => 'test query'
                                ]
                            ],
                            'filter' => [
                                'bool' => [
                                    'must' => [
                                        [
                                            'term' => [
                                                'price' => 100
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

        $builder = (new SearchBuilder($model, 'test query'))->where('price', 100);

        $this->initEngine()
            ->search($builder);
    }
}