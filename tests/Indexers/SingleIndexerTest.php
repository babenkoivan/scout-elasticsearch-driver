<?php

namespace ScoutElastic\Tests\Indexers;

use ScoutElastic\Tests\Config;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Indexers\SingleIndexer;

class SingleIndexerTest extends AbstractIndexerTest
{
    public function testUpdateWithDisabledSoftDelete()
    {
        Config::set('scout.soft_delete', false);

        ElasticClient
            ::shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 1,
                'body' => [
                    'name' => 'foo',
                ],
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 2,
                'body' => [
                    'name' => 'bar',
                ],
            ]);

        (new SingleIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testUpdateWithEnabledSoftDelete()
    {
        Config::set('scout.soft_delete', true);

        ElasticClient
            ::shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 1,
                'body' => [
                    'name' => 'foo',
                    '__soft_deleted' => 1,
                ],
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 2,
                'body' => [
                    'name' => 'bar',
                    '__soft_deleted' => 0,
                ],
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 3,
                'body' => [
                    '__soft_deleted' => 0,
                ],
            ]);

        (new SingleIndexer())
            ->update($this->models);

        $this->addToAssertionCount(1);
    }

    public function testUpdateWithSpecifiedDocumentRefreshOption()
    {
        Config::set('scout_elastic.document_refresh', 'true');

        ElasticClient
            ::shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'refresh' => 'true',
                'id' => 1,
                'body' => [
                    'name' => 'foo',
                ],
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'refresh' => 'true',
                'id' => 2,
                'body' => [
                    'name' => 'bar',
                ],
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
                'id' => 1,
                'client' => [
                    'ignore' => 404,
                ],
            ])
            ->shouldReceive('delete')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 2,
                'client' => [
                    'ignore' => 404,
                ],
            ])
            ->shouldReceive('delete')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 3,
                'client' => [
                    'ignore' => 404,
                ],
            ]);

        (new SingleIndexer())
            ->delete($this->models);

        $this->addToAssertionCount(1);
    }
}
