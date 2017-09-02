<?php

namespace ScoutElastic\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Searchable;

class ModelStub extends Model
{
    use Searchable;

    protected $table = 'test_table';

    protected $primaryKey = 'id';

    protected $indexConfigurator = IndexConfiguratorStub::class;

    protected $mapping = [
        'properties' => [
            'id' => [
                'type' => 'integer',
                'index' => 'not_analyzed',
            ],
            'test_field' => [
                'type' => 'string',
                'analyzer' => 'standard'
            ]
        ]
    ];
}