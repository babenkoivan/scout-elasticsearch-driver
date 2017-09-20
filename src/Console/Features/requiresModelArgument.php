<?php

namespace ScoutElastic\Console\Features;

use Exception;
use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Searchable;
use ScoutElastic\SearchableModel;
use Symfony\Component\Console\Input\InputArgument;

trait requiresModelArgument
{
    protected function getModel()
    {
        $modelClass = trim($this->argument('model'));

        $modelInstance = new $modelClass;

        if (!($modelInstance instanceof Model) || !in_array(Searchable::class, class_uses_recursive($modelClass))) {
            $this->error(sprintf(
                'The %s class must extend %s and use the %s trait.',
                $modelClass,
                Model::class,
                Searchable::class
            ));

            return null;
        }

        return $modelInstance;
    }

    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The model class'],
        ];
    }
}