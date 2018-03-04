<?php

namespace ScoutElastic\Tests\Indexers;

use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;
use ScoutElastic\Tests\Dependencies\Model;

abstract class AbstractIndexerTest extends TestCase
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
                'searchable_array' => [
                    'name' => 'foo'
                ]
            ]),
            $this->mockModel([
                'key' => 2,
                'searchable_array' => [
                    'name' => 'bar'
                ]
            ]),
            $this->mockModel([
                'key' => 3,
                'searchable_array' => []
            ])
        ]);
    }
}