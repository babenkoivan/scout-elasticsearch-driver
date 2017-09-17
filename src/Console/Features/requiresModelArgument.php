<?php

namespace ScoutElastic\Console\Features;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Searchable;
use Symfony\Component\Console\Input\InputArgument;

trait requiresModelArgument
{
    /**
     * @return Model|null
     */
    protected function getModel()
    {
        $modelClass = trim($this->argument('model'));

        $modelInstance = new $modelClass;

        if (!($modelInstance instanceof Model) || !method_exists($modelInstance, 'getIndexConfigurator')) {
            throw new InvalidArgumentException(sprintf(
                'The %s class must extend %s and use the %s trait.',
                $modelClass,
                Model::class,
                Searchable::class
            ));
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