<?php

namespace ScoutElastic\Payloads;

use Exception;
use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Searchable;

class TypePayload extends IndexPayload
{
    /**
     * The model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * TypePayload constructor.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @throws \Exception
     * @return void
     */
    public function __construct(Model $model)
    {
        if (! in_array(Searchable::class, class_uses_recursive($model))) {
            throw new Exception(sprintf(
                'The %s model must use the %s trait.',
                get_class($model),
                Searchable::class
            ));
        }

        $this->model = $model;

        parent::__construct($model->getIndexConfigurator());

        $this->payload['type'] = $model->searchableAs();
        $this->protectedKeys[] = 'type';
    }
}
