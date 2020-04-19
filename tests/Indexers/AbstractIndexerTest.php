<?php

namespace ScoutElastic\Tests\Indexers;

use Illuminate\Database\Eloquent\Collection;
use ScoutElastic\Tests\AbstractTestCase;
use ScoutElastic\Tests\Dependencies\Model;

abstract class AbstractIndexerTest extends AbstractTestCase
{
    use Model;

    /**
     * @var Collection
     */
    protected $models;

    protected function setUp()
    {
        $this->models = new Collection([
            $this->mockModel([
                'key' => 1,
                'trashed' => true,
                'searchable_array' => [
                    'name' => 'foo',
                ],
            ]),
            $this->mockModel([
                'key' => 2,
                'trashed' => false,
                'searchable_array' => [
                    'name' => 'bar',
                ],
            ]),
            $this->mockModel([
                'key' => 3,
                'trashed' => false,
                'searchable_array' => [],
            ]),
            $this->mockModel([
                'key' => 4,
                'trashed' => false,
                'scoutMetadata' => [
                    'name' => 'bar',
                    '_routing' => 'woo',
                ],
            ]),
        ]);
    }
}
