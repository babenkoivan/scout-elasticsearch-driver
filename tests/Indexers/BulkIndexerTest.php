<?php

namespace ScoutElastic\Tests\Indexers;

use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Indexers\BulkIndexer;

class BulkIndexerTest extends AbstractIndexerTest
{
    public function testUpdate()
    {
        ElasticClient
            ::shouldReceive('bulk')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'body' => [
                    ['index' => ['_id' => 1]],
                    ['name' => 'foo'],
                    ['index' => ['_id' => 2]],
                    ['name' => 'bar']
                ]
            ]);

        (new BulkIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testDelete()
    {
        ElasticClient
            ::shouldReceive('bulk')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'body' => [
                    ['delete' => ['_id' => 1]],
                    ['delete' => ['_id' => 2]],
                    ['delete' => ['_id' => 3]]
                ]
            ]);

        (new BulkIndexer())
            ->delete($this->models);

        $this->addToAssertionCount(1);
    }
}