<?php

namespace ScoutElastic\Console\Features;

use ScoutElastic\Searchable;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Input\InputArgument;

trait RequiresModelArgument
{
    /**
     * Get the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getModel()
    {
        $modelClass = trim($this->argument('model'));

        $modelInstance = new $modelClass;

        if (
            ! ($modelInstance instanceof Model) ||
            ! in_array(Searchable::class, class_uses_recursive($modelClass))
        ) {
            throw new InvalidArgumentException(sprintf(
                'The %s class must extend %s and use the %s trait.',
                $modelClass,
                Model::class,
                Searchable::class
            ));
        }

        return $modelInstance;
    }

    /**
     * Get the arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'model',
                InputArgument::REQUIRED,
                'The model class',
            ],
        ];
    }
}
