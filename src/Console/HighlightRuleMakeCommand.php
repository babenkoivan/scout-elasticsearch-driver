<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class HighlightRuleMakeCommand extends GeneratorCommand
{
    protected $name = 'make:highlight-rule';

    protected $description = 'Create a new highlight rule';

    protected $type = 'Rule';

    public function getStub()
    {
        return __DIR__.'/stubs/highlight_rule.stub';
    }
}