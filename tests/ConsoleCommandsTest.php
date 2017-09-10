<?php

namespace ScoutElastic\Tests;

use Mockery;
use ScoutElastic\Console\ElasticIndexCreateCommand;
use ScoutElastic\Console\ElasticIndexDropCommand;
use ScoutElastic\Console\ElasticIndexUpdateCommand;
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
            ->andReturnNull()
            ->getMock();

        foreach ($arguments as $key => $value) {
            $command->shouldReceive('argument')
                ->with($key)
                ->andReturn($value)
                ->getMock();
        }

        $command->handleCommand();

        $this->addToAssertionCount(1);
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
            ]);

        $this->fireCommand(ElasticIndexCreateCommand::class, [
            'index-configurator' => IndexConfiguratorStub::class
        ]);

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
            ]);

        $this->fireCommand(ElasticIndexUpdateCommand::class, [
            'index-configurator' => IndexConfiguratorStub::class
        ]);
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
    }
}