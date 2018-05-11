<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class SuggestRuleMakeCommand extends GeneratorCommand
{
    protected $name = 'make:suggest-rule';

    protected $description = 'Create a new suggest rule';

    protected $type = 'Rule';

    public function getStub()
    {
        return __DIR__.'/stubs/suggest_rule.stub';
    }
}