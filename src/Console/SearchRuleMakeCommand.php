<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class SearchRuleMakeCommand extends GeneratorCommand
{
    protected $name = 'make:search-rule';

    protected $description = 'Create a new search rule';

    protected $type = 'Rule';

    public function getStub()
    {
        return __DIR__.'/stubs/search_rule.stub';
    }
}