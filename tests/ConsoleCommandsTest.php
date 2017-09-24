<?php

namespace ScoutElastic\Tests;

use Mockery;
use ScoutElastic\Console\ElasticIndexCreateCommand;
use ScoutElastic\Console\ElasticIndexDropCommand;
use ScoutElastic\Console\ElasticIndexUpdateCommand;
use ScoutElastic\Console\ElasticMigrateCommand;
use ScoutElastic\Console\ElasticUpdateMappingCommand;
use ScoutElastic\Tests\Stubs\IndexConfiguratorStub;
use ScoutElastic\Tests\Stubs\ModelStub;

class ConsoleCommandsTest extends TestCase
{
    private function fireCommand($class, array $arguments)
    {
        $command = Mockery::mock($class)
            ->makePartial()

            ->shouldReceive('line')
            ->getMock()

            ->shouldReceive('call')
            ->getMock();

        foreach ($arguments as $key => $value) {
            $command->shouldReceive('argument')
                ->with($key)
                ->andReturn($value)
                ->getMock();
        }

        $command->handle();
    }

    public function test_if_the_create_index_command_builds_correct_response()
    {
        $this->mockClient()

            ->shouldReceive('indices')
            ->andReturnSelf()
            ->getMock()

            ->shouldReceive('create')
            ->with([
                'index' => 'test_index',
                'body' => [
                    'settings' => [
                        'analysis' => [
                            'analyzer' => [
                                'test_analyzer' => [
                                    'type' => 'custom',
                                    'tokenizer' => 'whitespace'
                                ]
                            ]
                        ]
                    ],
                    'mappings' => [
                        '_default_' => [
                            'properties' => [
                                'test_default_field' => [
                                    'type' => 'string',
                                    'analyzer' => 'test_analyzer'
                                ]
                            ]
                        ]
                    ]
                ]
            ])

            ->shouldReceive('putAlias')
            ->with([
                'index' => 'test_index',
                'name' => 'test_index_write'
            ]);

        $this->fireCommand(ElasticIndexCreateCommand::class, [
            'index-configurator' => IndexConfiguratorStub::class
        ]);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_update_index_command_builds_correct_response()
    {
        $this->mockClient()

            ->shouldReceive('indices')
            ->andReturnSelf()
            ->getMock()

            ->shouldReceive('exists')
            ->andReturn(true)
            ->getMock()

            ->shouldReceive('close')
            ->andReturnNull()
            ->getMock()

            ->shouldReceive('open')
            ->andReturnNull()
            ->getMock()

            ->shouldReceive('putSettings')
            ->with([
                'index' => 'test_index',
                'body' => [
                    'settings' => [
                        'analysis' => [
                            'analyzer' => [
                                'test_analyzer' => [
                                    'type' => 'custom',
                                    'tokenizer' => 'whitespace'
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->getMock()

            ->shouldReceive('putMapping')
            ->with([
                'index' => 'test_index',
                'type' => '_default_',
                'body' => [
                    '_default_' => [
                        'properties' => [
                            'test_default_field' => [
                                'type' => 'string',
                                'analyzer' => 'test_analyzer'
                            ]
                        ]
                    ]
                ]
            ])
            ->getMock()

            ->shouldReceive('existsAlias')
            ->with([
                'name' => 'test_index_write'
            ])
            ->andReturn(false)
            ->getMock()

            ->shouldReceive('putAlias')
            ->with([
                'index' => 'test_index',
                'name' => 'test_index_write'
            ]);

        $this->fireCommand(ElasticIndexUpdateCommand::class, [
            'index-configurator' => IndexConfiguratorStub::class
        ]);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_drop_index_command_builds_correct_response()
    {
        $this->mockClient()

            ->shouldReceive('indices')
            ->andReturnSelf()
            ->getMock()

            ->shouldReceive('delete')
            ->with([
                'index' => 'test_index',
            ]);

        $this->fireCommand(ElasticIndexDropCommand::class, [
            'index-configurator' => IndexConfiguratorStub::class
        ]);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_update_mapping_command_builds_correct_response()
    {
        $this->mockClient()

            ->shouldReceive('indices')
            ->andReturnSelf()
            ->getMock()

            ->shouldReceive('putMapping')
            ->with([
                'index' => 'test_index',
                'type' => 'test_table',
                'body' => [
                    'test_table' => [
                        'properties' => [
                            'test_default_field' => [
                                'type' => 'string',
                                'analyzer' => 'test_analyzer'
                            ],
                            'id' => [
                                'type' => 'integer',
                                'index' => 'not_analyzed'
                            ],
                            'test_field' => [
                                'type' => 'string',
                                'analyzer' => 'standard'
                            ]
                        ]
                    ]
                ]
            ]);

        $this->fireCommand(ElasticUpdateMappingCommand::class, [
            'model' => ModelStub::class
        ]);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_migrate_command_builds_correct_payload_for_new_target_index()
    {
        $this->mockClient()

            ->shouldReceive('indices')
            ->andReturnSelf()
            ->getMock()

            ->shouldReceive('exists')
            ->with([
                'index' => 'test_index_v2'
            ])
            ->andReturn(false)
            ->getMock()

            ->shouldReceive('create')
            ->with([
                'index' => 'test_index_v2',
                'body' => [
                    'settings' => [
                        'analysis' => [
                            'analyzer' => [
                                'test_analyzer' => [
                                    'type' => 'custom',
                                    'tokenizer' => 'whitespace'
                                ]
                            ]
                        ]
                    ],
                    'mappings' => [
                        '_default_' => [
                            'properties' => [
                                'test_default_field' => [
                                    'type' => 'string',
                                    'analyzer' => 'test_analyzer'
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->getMock()

            ->shouldReceive('putMapping')
            ->with([
                'index' => 'test_index_v2',
                'type' => 'test_table',
                'body' => [
                    'test_table' => [
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'index' => 'not_analyzed',
                            ],
                            'test_field' => [
                                'type' => 'string',
                                'analyzer' => 'standard'
                            ],
                            'test_default_field' => [
                                'type' => 'string',
                                'analyzer' => 'test_analyzer'
                            ]
                        ]
                    ]
                ]
            ])
            ->getMock()

            ->shouldReceive('existsAlias')
            ->with([
                'name' => 'test_index_write',
            ])
            ->andReturn(true)
            ->getMock()

            ->shouldReceive('getAlias')
            ->with([
                'name' => 'test_index_write'
            ])
            ->andReturn([
                'test_index' => [
                    'aliases' => [
                        'test_index_write' => [

                        ]
                    ]
                ]
            ])
            ->getMock()

            ->shouldReceive('deleteAlias')
            ->with([
                'index' => 'test_index',
                'name' => 'test_index_write'
            ])
            ->getMock()

            ->shouldReceive('putAlias')
            ->with([
                'index' => 'test_index_v2',
                'name' => 'test_index_write'
            ])
            ->getMock()

            ->shouldReceive('delete')
            ->with([
                'index' => 'test_index'
            ])
            ->getMock()

            ->shouldReceive('existsAlias')
            ->with([
                'name' => 'test_index',
            ])
            ->andReturn(false)
            ->getMock()

            ->shouldReceive('putAlias')
            ->with([
                'index' => 'test_index_v2',
                'name' => 'test_index'
            ]);

        $this->fireCommand(ElasticMigrateCommand::class, [
            'model' => ModelStub::class,
            'test_index_v2'
        ]);

        $this->addToAssertionCount(1);
    }

    public function test_if_the_migrate_command_builds_correct_payload_for_existing_target_index()
    {
        $this->mockClient()

            ->shouldReceive('indices')
            ->andReturnSelf()
            ->getMock()

            ->shouldReceive('exists')
            ->with([
                'index' => 'test_index_v2'
            ])
            ->andReturn(true)
            ->getMock()

            ->shouldReceive('close')
            ->with([
                'index' => 'test_index_v2'
            ])
            ->getMock()

            ->shouldReceive('putSettings')
            ->with([
                'index' => 'test_index_v2',
                'body' => [
                    'settings' => [
                        'analysis' => [
                            'analyzer' => [
                                'test_analyzer' => [
                                    'type' => 'custom',
                                    'tokenizer' => 'whitespace'
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->getMock()

            ->shouldReceive('putMapping')
            ->with([
                'index' => 'test_index_v2',
                'type' => '_default_',
                'body' => [
                    '_default_' => [
                        'properties' => [
                            'test_default_field' => [
                                'type' => 'string',
                                'analyzer' => 'test_analyzer'
                            ]
                        ]
                    ]
                ]
            ])
            ->getMock()

            ->shouldReceive('open')
            ->with([
                'index' => 'test_index_v2'
            ])
            ->getMock()

            ->shouldReceive('putMapping')
            ->with([
                'index' => 'test_index_v2',
                'type' => 'test_table',
                'body' => [
                    'test_table' => [
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'index' => 'not_analyzed',
                            ],
                            'test_field' => [
                                'type' => 'string',
                                'analyzer' => 'standard'
                            ],
                            'test_default_field' => [
                                'type' => 'string',
                                'analyzer' => 'test_analyzer'
                            ]
                        ]
                    ]
                ]
            ])
            ->getMock()

            ->shouldReceive('existsAlias')
            ->with([
                'name' => 'test_index_write',
            ])
            ->andReturn(true)
            ->getMock()

            ->shouldReceive('getAlias')
            ->with([
                'name' => 'test_index_write'
            ])
            ->andReturn([
                'test_index' => [
                    'aliases' => [
                        'test_index_write' => [

                        ]
                    ]
                ]
            ])
            ->getMock()

            ->shouldReceive('deleteAlias')
            ->with([
                'index' => 'test_index',
                'name' => 'test_index_write'
            ])
            ->getMock()

            ->shouldReceive('putAlias')
            ->with([
                'index' => 'test_index_v2',
                'name' => 'test_index_write'
            ])
            ->getMock()

            ->shouldReceive('delete')
            ->with([
                'index' => 'test_index'
            ])
            ->getMock()

            ->shouldReceive('existsAlias')
            ->with([
                'name' => 'test_index',
            ])
            ->andReturn(false)
            ->getMock()

            ->shouldReceive('putAlias')
            ->with([
                'index' => 'test_index_v2',
                'name' => 'test_index'
            ]);

        $this->fireCommand(ElasticMigrateCommand::class, [
            'model' => ModelStub::class,
            'test_index_v2'
        ]);

        $this->addToAssertionCount(1);
    }
}