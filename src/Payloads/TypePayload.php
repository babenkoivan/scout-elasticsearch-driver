<?php

namespace ScoutElastic\Payloads;

use Exception;
use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Searchable;

class TypePayload extends IndexPayload
{
    protected $protectedKeys = [
        'index',
        'type'
    ];

    protected $model;

    public function __construct(Model $model)
    {
        if (!method_exists($model, 'getIndexConfigurator')) {
            throw new Exception(sprintf(
                'The %s model must use the %s trait.',
                get_class($model),
                Searchable::class
            ));
        }

        $this->model = $model;

        parent::__construct($model->getIndexConfigurator());

        $this->payload['type'] = $model->searchableAs();
    }
}