<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class AggregateRuleMakeCommand extends GeneratorCommand
{
    protected $name = 'make:aggregate-rule';

    protected $description = 'Create a new aggregate rule';

    protected $type = 'Rule';

    public function getStub()
    {
        return __DIR__.'/stubs/aggregate_rule.stub';
    }
}