<?php

namespace ScoutElastic\Tests\Stubs;

use ScoutElastic\IndexConfigurator;
use ScoutElastic\Migratable;

class IndexConfiguratorStub extends IndexConfigurator
{
    use Migratable;

    protected $name = 'test_index';

    protected $settings = [
        'analysis' => [
            'analyzer' => [
                'test_analyzer' => [
                    'type' => 'custom',
                    'tokenizer' => 'whitespace'
                ]
            ]
        ]
    ];

    protected $defaultMapping = [
        'properties' => [
            'test_default_field' => [
                'type' => 'string',
                'analyzer' => 'test_analyzer'
            ]
        ]
    ];
}