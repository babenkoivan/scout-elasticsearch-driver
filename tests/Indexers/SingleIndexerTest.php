<?php

namespace ScoutElastic\Tests\Indexers;

use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Indexers\SingleIndexer;

class SingleIndexerTest extends AbstractIndexerTest
{
    public function testUpdate()
    {
        ElasticClient
            ::shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 1,
                'body' => [
                    'name' => 'foo'
                ]
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 2,
                'body' => [
                    'name' => 'bar'
                ]
            ]);

        (new SingleIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testDelete()
    {
        ElasticClient
            ::shouldReceive('delete')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 1
            ])
            ->shouldReceive('delete')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 2
            ])
            ->shouldReceive('delete')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 3
            ]);

        (new SingleIndexer())
            ->delete($this->models);

        $this->addToAssertionCount(1);
    }
}