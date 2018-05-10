<?php

namespace ScoutElastic\Console;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class SearchableModelMakeCommand extends ModelMakeCommand
{
    /**
     * @var string
     */
    protected $name = 'make:searchable-model';

    /**
     * @var string
     */
    protected $description = 'Create a new searchable model';

    /**
     * @inheritdoc
     */
    public function getStub()
    {
        return __DIR__ . '/stubs/searchable_model.stub';
    }

    /**
     * @inheritdoc
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        $options[] = [
            'index-configurator',
            'i',
            InputOption::VALUE_REQUIRED,
            'Specify the index configurator for the model. It\'ll be created if doesn\'t exist.'
        ];

        $options[] = [
            'search-rule',
            's',
            InputOption::VALUE_REQUIRED,
            'Specify the search rule for the model. It\'ll be created if doesn\'t exist.'
        ];

        return $options;
    }

    /**
     * @return string
     */
    protected function getIndexConfigurator()
    {
        return trim($this->option('index-configurator'));
    }

    /**
     * @return string
     */
    protected function getSearchRule()
    {
        return trim($this->option('search-rule'));
    }

    /**
     * @param string $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $indexConfigurator = $this->getIndexConfigurator();

        $stub = str_replace(
            'DummyIndexConfigurator',
            $indexConfigurator ? "{$indexConfigurator}::class" : 'null', $stub
        );

        $searchRule = $this->getSearchRule();

        $stub = str_replace(
            'DummySearchRule',
            $searchRule ? "{$searchRule}::class" : '//', $stub
        );

        return $stub;
    }

    public function handle()
    {
        $indexConfigurator = $this->getIndexConfigurator();

        if ($indexConfigurator && !$this->alreadyExists($indexConfigurator)) {
            $this->call('make:index-configurator', [
                'name' => $indexConfigurator
            ]);
        }

        $searchRule = $this->getSearchRule();

        if ($searchRule && !$this->alreadyExists($searchRule)) {
            $this->call('make:search-rule', [
                'name' => $searchRule
            ]);
        }

        parent::handle();
    }
}
