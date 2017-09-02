<?php

namespace ScoutElastic\Payloads;

use Exception;
use Illuminate\Database\Eloquent\Model;

class DocumentPayload extends TypePayload
{
    protected $protectedKeys = [
        'index',
        'type',
        'id'
    ];

    public function __construct(Model $model)
    {
        if (!$model->getKey()) {
            throw new Exception(sprintf(
                'The key value must be set to construct a payload for the %s instance.',
                get_class($model)
            ));
        }

        parent::__construct($model);

        $this->payload['id'] = $model->getKey();
    }
}