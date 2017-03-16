<?php

namespace ScoutElastic\Console;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class SearchableModelMakeCommand extends ModelMakeCommand
{
    protected $name = 'make:searchable-model';

    protected $description = 'Create a new searchable model';

    public function getStub()
    {
        return __DIR__.'/stubs/searchable_model.stub';
    }

    protected function getOptions()
    {
        $options = parent::getOptions();

        $options[] = ['index-configurator', 'i', InputOption::VALUE_REQUIRED,
            'Specify the index configurator for the model. It\'ll be created if doesn\'t exist.'];

        $options[] = ['search-rule', 's', InputOption::VALUE_REQUIRED,
            'Specify the search rule for the model. It\'ll be created if doesn\'t exist.'];

        return $options;
    }

    protected function getIndexConfigurator()
    {
        return trim($this->option('index-configurator'));
    }

    protected function getSearchRule()
    {
        return trim($this->option('search-rule'));
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);


        $indexConfigurator = $this->getIndexConfigurator();

        if ($indexConfigurator) {
            $stub = str_replace('DummyIndexConfigurator', "protected \$indexConfigurator = {$indexConfigurator}::class;", $stub);
        } else {
            $stub = preg_replace('#DummyIndexConfigurator\s+#', '', $stub);
        }


        $searchRule = $this->getSearchRule();

        if ($searchRule) {
            $stub = str_replace('DummySearchRules', "protected \$searchRules = [\n        {$searchRule}::class\n    ];", $stub);
        } else {
            $stub = preg_replace('#DummySearchRules\s+#', '', $stub);
        }

        return $stub;
    }

    public function fire()
    {
        $indexConfigurator = $this->getIndexConfigurator();

        if (!$this->alreadyExists($indexConfigurator)) {
            $this->call('make:index-configurator', [
                'name' => $indexConfigurator
            ]);
        }


        $searchRule = $this->getSearchRule();

        if (!$this->alreadyExists($searchRule)) {
            $this->call('make:search-rule', [
                'name' => $searchRule
            ]);
        }

        
        parent::fire();
    }
}
