<?php

namespace ScoutElastic\Console;

use Illuminate\Console\GeneratorCommand;

class SearchRuleMakeCommand extends GeneratorCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'make:search-rule';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a new search rule';

    /**
     * {@inheritdoc}
     */
    protected $type = 'Rule';

    /**
     * {@inheritdoc}
     */
    public function getStub()
    {
        return __DIR__.'/stubs/search_rule.stub';
    }
}
