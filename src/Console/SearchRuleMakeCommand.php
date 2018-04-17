<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class SearchRuleMakeCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'make:search-rule';

    /**
     * @var string
     */
    protected $description = 'Create a new search rule';

    /**
     * @var string
     */
    protected $type = 'Rule';

    /**
     * @inheritdoc
     */
    public function getStub()
    {
        return __DIR__ . '/stubs/search_rule.stub';
    }
}