<?php

namespace ScoutElastic\Tests\Indexers;

use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Indexers\SingleIndexer;
use ScoutElastic\Tests\Config;

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
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 4,
                'body' => [
                    'name' => 'bar',
                ],
                'routing' => 'woo',
            ]);

        (new SingleIndexer)
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
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 4,
                'body' => [
                    'name' => 'bar',
                    '__soft_deleted' => 0,
                ],
                'routing' => 'woo',
            ]);

        (new SingleIndexer)
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
            ])
            ->shouldReceive('index')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 4,
                'body' => [
                    'name' => 'bar',
                ],
                'routing' => 'woo',
            ]);

        (new SingleIndexer)
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
            ])
            ->shouldReceive('delete')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 4,
                'client' => [
                    'ignore' => 404,
                ],
            ]);

        (new SingleIndexer)
            ->delete($this->models);

        $this->addToAssertionCount(1);
    }

    public function testDeleteWithSpecifiedDocumentRefreshOption()
    {
        Config::set('scout_elastic.document_refresh', true);

        ElasticClient
            ::shouldReceive('delete')
            ->once()
            ->with([
                'index' => 'test',
                'type' => 'test',
                'id' => 1,
                'refresh' => true,
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
                'refresh' => true,
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
                'refresh' => true,
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
                'refresh' => true,
                'client' => [
                    'ignore' => 404,
                ],
            ]);

        (new SingleIndexer())
                ->delete($this->models);

        $this->addToAssertionCount(1);
    }
}
